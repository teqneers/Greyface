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
use DateTimeImmutable;
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
        $before = $request->query->get('before');
        $response = $connectRepository->findFiltered($user, $query, $start, $max, $sortBy, boolval($desc), $before ?: null);
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
        $data = json_decode($request->getContent(), true) ?? [];
        $greylist = $this->findEntry($connectRepository, $data);
        $snapshot = $this->snapshot($greylist);

        $result = $this->moveToWhiteList($greylist, $validator, $connectRepository, $emailAutoWhiteListRepository);
        if ($result instanceof Response) {
            return $result;
        }

        // The snapshot and the flag let the client undo exactly this move.
        return new JsonResponse([
            'message' => 'Data have been moved to whitelist!',
            'entry' => $snapshot,
            'awlCreated' => $result,
        ]);
    }

    /**
     * Moves several entries at once; the body is {"entries": [{name, domain, source, rcpt}, ...]}.
     * Entries that no longer exist are skipped rather than failing the batch.
     */
    #[Route('/bulk/toWhiteList', methods: ['POST'])]
    #[IsGranted('EMAIL_AUTOWHITE_CREATE')]
    public function bulkToWhiteList(
        Request                      $request,
        ValidatorInterface           $validator,
        ConnectRepository            $connectRepository,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $moved = 0;
        foreach ($this->entriesFromRequest($request) as $data) {
            $greylist = $connectRepository->find($data);
            if (!$greylist) {
                continue;
            }
            $result = $this->moveToWhiteList($greylist, $validator, $connectRepository, $emailAutoWhiteListRepository);
            if ($result instanceof Response) {
                return $result;
            }
            $moved++;
        }
        return new JsonResponse(['moved' => $moved]);
    }

    /**
     * Reverses toWhiteList(): puts the entry back with its original timestamp
     * and removes the auto-whitelist row if that call created it.
     */
    #[Route('/undoToWhiteList', methods: ['POST'])]
    #[IsGranted('CONNECT_CREATE')]
    public function undoToWhiteList(
        Request                      $request,
        ConnectRepository            $connectRepository,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $entry = $data['entry'] ?? [];
        $key = [
            'name' => $entry['name'] ?? '',
            'domain' => $entry['domain'] ?? '',
            'source' => $entry['source'] ?? '',
            'rcpt' => $entry['rcpt'] ?? '',
        ];
        if (in_array('', $key, true)) {
            return new JsonResponse(['error' => 'Entry is incomplete'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$connectRepository->find($key)) {
            $restored = Connect::create($key['name'], $key['domain'], $key['source'], $key['rcpt']);
            if (!empty($entry['firstSeen'])) {
                $restored->setFirstSeenAt(new DateTimeImmutable($entry['firstSeen']));
            }
            $connectRepository->save($restored);
        } else {
            $restored = $connectRepository->find($key);
        }

        if (!empty($data['removeAwl'])) {
            [$senderDomain, $senderName] = $this->normalize_sender($restored);
            $awl = $emailAutoWhiteListRepository->find([
                'name' => $senderName,
                'domain' => $senderDomain,
                'source' => $restored->getSource(),
            ]);
            if ($awl) {
                $emailAutoWhiteListRepository->delete($awl);
            }
        }

        return new JsonResponse(['message' => 'Entry restored']);
    }

    /**
     * Deletes several entries at once; the body is {"entries": [{name, domain, source, rcpt}, ...]}.
     */
    #[Route('/bulk/delete', methods: ['DELETE'])]
    #[IsGranted('CONNECT_DELETE')]
    public function bulkDelete(
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $deleted = 0;
        foreach ($this->entriesFromRequest($request) as $data) {
            $greylist = $connectRepository->find($data);
            if ($greylist) {
                $connectRepository->delete($greylist);
                $deleted++;
            }
        }
        return new JsonResponse(['deleted' => $deleted]);
    }

    /**
     * @return bool whether a new auto-whitelist row was created
     */
    private function moveToWhiteList(
        Connect                      $greylist,
        ValidatorInterface           $validator,
        ConnectRepository            $connectRepository,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): bool|Response
    {
        [$sender_domain, $deverp_sender_name] = $this->normalize_sender($greylist);
        $isAlreadyInWhitelist = $emailAutoWhiteListRepository->find([
            'name' => $deverp_sender_name,
            'domain' => $sender_domain,
            'source' => $greylist->getSource()
        ]);
        $created = false;
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
            $created = true;
        }
        $connectRepository->delete($greylist);
        return $created;
    }

    private function findEntry(ConnectRepository $connectRepository, array $data): Connect
    {
        $key = [
            'name' => $data['name'] ?? '',
            'domain' => $data['domain'] ?? '',
            'source' => $data['source'] ?? '',
            'rcpt' => $data['rcpt'] ?? ''
        ];
        $greylist = $connectRepository->find($key);
        if (!$greylist) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $key['name'] . ', Domain ' . $key['domain'] . ' and Source ' . $key['source'] . ' and Rcpt ' . $key['rcpt']
            );
        }
        return $greylist;
    }

    /**
     * @return array<int, array{name: string, domain: string, source: string, rcpt: string}>
     */
    private function entriesFromRequest(Request $request): array
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $entries = [];
        foreach ($data['entries'] ?? [] as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $entries[] = [
                'name' => (string)($entry['name'] ?? ''),
                'domain' => (string)($entry['domain'] ?? ''),
                'source' => (string)($entry['source'] ?? ''),
                'rcpt' => (string)($entry['rcpt'] ?? ''),
            ];
        }
        return $entries;
    }

    private function snapshot(Connect $greylist): array
    {
        return [
            'name' => $greylist->getName(),
            'domain' => $greylist->getDomain(),
            'source' => $greylist->getSource(),
            'rcpt' => $greylist->getRcpt(),
            'firstSeen' => $greylist->getFirstSeen()->format(DateTimeImmutable::ATOM),
        ];
    }

    #[Route('/delete', methods: ['DELETE'])]
    #[IsGranted('CONNECT_DELETE')]
    public function delete(
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $greylist = $this->findEntry($connectRepository, $data);
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

        if (!empty($data['date'])) {
            $date = date_format(new DateTime($data['date']), 'Y-m-d');

            $deleted = $connectRepository->deleteByDate($date);
            return new JsonResponse(['deleted' => $deleted]);
        }
        return new JsonResponse(['error' => 'Date is missing!'], Response::HTTP_UNPROCESSABLE_ENTITY);
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
