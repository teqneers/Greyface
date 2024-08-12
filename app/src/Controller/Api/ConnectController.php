<?php

namespace App\Controller\Api;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Domain\Entity\Connect\Connect;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\UserInterface;
use App\Messenger\Validation;
use DateTime;
use OutOfBoundsException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/greylist')]
class ConnectController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('CONNECT_LIST')]
    public function list(
        Security          $security,
        Request           $request,
        UserRepository    $userRepository,
        ConnectRepository $connectRepository
    ): Response
    {
        $currentUser = $security->getUser();
        $isAdmin = $currentUser instanceof UserInterface && $currentUser->isAdministrator();

        $user = $userRepository->findById($currentUser->getId());
        $userFilter = $request->query->get('user');
        if ($isAdmin) {
            if (!$userFilter) {
                $user = null;
            } else if ($userFilter === 'show_unassigned') {
                $user = $userFilter;
            } else {
                $user = $userRepository->findById($userFilter);
            }
        }

        $query = $request->query->get('query');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $sortBy = $request->query->get('sortBy');
        $desc = $request->query->get('desc');
        $response = $connectRepository->findAll($user, $query, $start, $max, $sortBy, boolval($desc));
        return new JsonResponse($response);
    }

    #[Route('/toWhiteList', methods: ['POST'])]
    #[IsGranted('EMAIL_AUTOWHITE_CREATE')]
    public function toWhiteList(
        Request                      $request,
        ValidatorInterface           $validator,
        ConnectRepository            $connectRepository,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $name = $data['name'] ?? '';
        $domain = $data['domain'] ?? '';
        $source = $data['source'] ?? '';
        $rcpt = $data['rcpt'] ?? '';

        $greylist = $connectRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source,
            'rcpt' => $rcpt
        ]);
        if (!$greylist) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source . ' and Rcpt ' . $rcpt
            );
        }

        [$sender_domain, $deverp_sender_name] = $this->normalize_sender($greylist);
        $isAlreadyInWhitelist = $emailAutoWhiteListRepository->find([
            'name' => $deverp_sender_name,
            'domain' => $sender_domain,
            'source' => $greylist->getSource()
        ]);
        if (!$isAlreadyInWhitelist) {
            $emailAwl = EmailAutoWhiteList::create(
                $deverp_sender_name, // sqlgrey is normalize_sender in from_awl table
                $sender_domain,
                $greylist->getSource(),
                $greylist->getFirstSeen(),
                $greylist->getFirstSeen());
            $errors = $validator->validate($emailAwl);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }

            $emailAutoWhiteListRepository->save($emailAwl);
        }
        $connectRepository->delete($greylist);
        return new JsonResponse('Data have been moved to whitelist!');
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('CONNECT_DELETE')]
    public function delete(
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $name = $data['name'] ?? '';
        $domain = $data['domain'] ?? '';
        $source = $data['source'] ?? '';
        $rcpt = $data['rcpt'] ?? '';

        $greylist = $connectRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source,
            'rcpt' => $rcpt
        ]);
        if (!$greylist) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source . ' and Rcpt ' . $rcpt
            );
        }
        $connectRepository->delete($greylist);
        return new JsonResponse('Domain deleted successfully!');
    }

    #[Route('/delete-to-date', methods: ['DELETE'])]
    #[IsGranted('CONNECT_DELETE')]
    public function deleteByTime(
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (isset($data['date'])) {
            $date = date_format(new DateTime($data['date']), 'Y-m-d');

            $connectRepository->deleteByDate($date);
            return new JsonResponse('Domain deleted successfully!');
        }
        return new JsonResponse('Date is missing!', 500);
    }

    // check https://github.com/jessereynolds/sqlgrey/blob/master/sqlgrey#L1166
    private function normalize_sender(Connect $greylist): array
    {
        $user = $greylist->getName();
        $domain = $greylist->getDomain();
        $rcpt = $greylist->getRcpt();

        return [
            substr($domain, 0, 255),
            substr($this->deverp_user($user, $rcpt), 0, 64)
        ];
    }

    // check https://github.com/jessereynolds/sqlgrey/blob/master/sqlgrey#L1166
    private function deverp_user(string $user, string $rcpt): string
    {
        // Try to match single-use addresses
        // SRS (first and subsequent levels of forwarding)
        $user = preg_replace('/^srs0=[^=]+=[^=]+=([^=]+)=([^=]+)$/', 'srs0=#=#=$1=$2', $user);
        $user = preg_replace('/^srs1=[^=]+=([^=]+)(=+)[^=]+=[^=]+=([^=]+)=([^=]+)$/', 'srs1=#=$1$2#=#=$3=$4', $user);

        // Strip extension, used sometimes for mailing-list VERP
        $user = preg_replace('/\+.*$/', '', $user);

        // Strip frequently used bounce/return masks
        $user = preg_replace('/((bo|bounce|notice-return|notice-reply)[._-])[0-9a-z-_.]+$/', '$1#', $user);

        // Strip hexadecimal sequences
        // At the beginning only if user will contain at least 4 consecutive alpha chars
        return preg_replace('/^[0-9a-f]{2,}(?=[._\/=-].*[a-z]{4,})|(?<=[._\/=-])[0-9a-f]+(?=[._\/=-]|$)/', '#', $user);
    }
}
