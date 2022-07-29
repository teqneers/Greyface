<?php

namespace App\Controller\Api;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\UserInterface;
use App\Messenger\Validation;
use DateTime;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
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
        if ($isAdmin) {
            $user = null;
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

        $isAlreadyInWhitelist = $emailAutoWhiteListRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source
        ]);

        if (!$isAlreadyInWhitelist) {
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

            $emailAwl = EmailAutoWhiteList::create(
                $greylist->getName(),
                $greylist->getDomain(),
                $greylist->getSource(),
                $greylist->getFirstSeen(),
                $greylist->getFirstSeen());
            $errors = $validator->validate($emailAwl);

            if (count($errors) > 0) {
                return Validation::getViolations($errors);
            }

            $emailAutoWhiteListRepository->save($emailAwl);
            $connectRepository->delete($greylist);
        }
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
}
