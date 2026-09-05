<?php

namespace App\Controller\Api;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Domain\Entity\Connect\Connect;
use App\Domain\Connect\ListTarget;
use App\Domain\Connect\MoveConnectToList;
use App\Domain\Connect\SenderAddress;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/greylist')]
class ConnectController
{
    public function __construct(
        private readonly SenderAddress     $senderAddress,
        private readonly MoveConnectToList $moveToList
    ) {
    }


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
    public function toWhiteList(
        Security                     $security,
        Request                      $request,
        ValidatorInterface           $validator,
        ConnectRepository            $connectRepository,
        EmailAutoWhiteListRepository $emailAutoWhiteListRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $greylist = $this->findEntry($connectRepository, $data);
        $this->assertMayAct($security, 'CONNECT_WHITELIST', $greylist);
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
    public function bulkToWhiteList(
        Security                     $security,
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
            // Per entry, not once for the batch: a caller must not be able to
            // smuggle somebody else's row in behind one of their own.
            $this->assertMayAct($security, 'CONNECT_WHITELIST', $greylist);
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
    public function undoToWhiteList(
        Security                     $security,
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

        // The row may no longer exist, so the check runs against what the caller
        // is asking to restore rather than against something already stored.
        $this->assertMayAct(
            $security,
            'CONNECT_WHITELIST',
            Connect::create($key['name'], $key['domain'], $key['source'], $key['rcpt'])
        );

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
            [$senderDomain, $senderName] = $this->senderAddress->parts($restored);
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
    public function bulkDelete(
        Security          $security,
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $deleted = 0;
        foreach ($this->entriesFromRequest($request) as $data) {
            $greylist = $connectRepository->find($data);
            if ($greylist) {
                $this->assertMayAct($security, 'CONNECT_DELETE', $greylist);
                $connectRepository->delete($greylist);
                $deleted++;
            }
        }
        return new JsonResponse(['deleted' => $deleted]);
    }

    /**
     * Sends entries to one of the other policy lists: the whitelist or blacklist,
     * for the sender or for its whole domain, or the domain auto-whitelist.
     *
     * One endpoint for one row and for fifty, because the interface offers the
     * same destinations from a row menu and from the selection bar, and two code
     * paths would eventually disagree about who is allowed what.
     *
     * The auto-whitelist-for-this-sender case is deliberately not here. It is the
     * one destination an ordinary user may reach, it carries its own undo, and it
     * is not worth destabilising to save a little duplication.
     */
    #[Route('/toList', methods: ['POST'])]
    public function toList(
        Security          $security,
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $target = ListTarget::tryFrom((string)($data['target'] ?? ''));
        if ($target === null) {
            return new JsonResponse(
                ['error' => 'Unknown target list'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Two separate questions, and both have to be asked. The destination
        // lists are administrators-only, and the greylist row still has to be
        // one the caller may touch.
        $this->assertMayReachList($security, $target);

        $moved = [];
        foreach ($this->entriesFromRequest($request) as $key) {
            $entry = $connectRepository->find($key);
            if (!$entry) {
                continue;
            }
            $this->assertMayAct($security, 'CONNECT_WHITELIST', $entry);

            $snapshot = $this->snapshot($entry);
            $moved[] = [
                'entry' => $snapshot,
                'created' => $this->moveToList->move($entry, $target),
            ];
        }

        return new JsonResponse([
            'moved' => count($moved),
            'target' => $target->value,
            'entries' => $moved,
        ]);
    }

    /**
     * Reverses toList(). Takes back exactly what that call returned, so an entry
     * that was already listed before the move is left listed.
     */
    #[Route('/undoToList', methods: ['POST'])]
    public function undoToList(
        Security $security,
        Request  $request
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $target = ListTarget::tryFrom((string)($data['target'] ?? ''));
        if ($target === null) {
            return new JsonResponse(
                ['error' => 'Unknown target list'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->assertMayReachList($security, $target);

        foreach ($data['entries'] ?? [] as $item) {
            $entry = is_array($item) ? ($item['entry'] ?? []) : [];
            $key = [
                'name' => (string)($entry['name'] ?? ''),
                'domain' => (string)($entry['domain'] ?? ''),
                'source' => (string)($entry['source'] ?? ''),
                'rcpt' => (string)($entry['rcpt'] ?? ''),
            ];
            if (in_array('', $key, true)) {
                return new JsonResponse(['error' => 'Entry is incomplete'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $restored = Connect::create($key['name'], $key['domain'], $key['source'], $key['rcpt']);
            if (!empty($entry['firstSeen'])) {
                $restored->setFirstSeenAt(new DateTimeImmutable($entry['firstSeen']));
            }
            $this->assertMayAct($security, 'CONNECT_WHITELIST', $restored);

            $this->moveToList->undo($restored, $target, (bool)($item['created'] ?? false));
        }

        return new JsonResponse(['message' => 'Entries restored']);
    }

    /**
     * Every destination writes to a list only administrators may edit. Checked
     * once for the request rather than per row: the answer cannot differ between
     * entries, and a partial batch would be worse than a clean refusal.
     */
    private function assertMayReachList(Security $security, ListTarget $target): void
    {
        if (!$security->isGranted($target->permission())) {
            throw new AccessDeniedException(
                sprintf('Not allowed to add entries to %s.', $target->value)
            );
        }
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
        [$sender_domain, $deverp_sender_name] = $this->senderAddress->parts($greylist);
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

    /**
     * ConnectController is not an AbstractController, so there is no
     * denyAccessUnlessGranted() to lean on and the row-level check is explicit.
     *
     * It is deliberately per row. The listing is filtered per user by
     * ConnectRepository, but the write endpoints take their identifiers straight
     * from the request body, so without this a caller could name any row in the
     * table, including one they cannot see.
     */
    private function assertMayAct(Security $security, string $attribute, Connect $entry): void
    {
        if (!$security->isGranted($attribute, $entry)) {
            throw new AccessDeniedException(
                sprintf('Not allowed to act on mail addressed to %s.', $entry->getRcpt())
            );
        }
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
    public function delete(
        Security          $security,
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $greylist = $this->findEntry($connectRepository, $data);
        $this->assertMayAct($security, 'CONNECT_DELETE', $greylist);
        $connectRepository->delete($greylist);
        return new JsonResponse('Domain deleted successfully!');
    }

    #[Route('/delete-to-date', methods: ['DELETE'])]
    #[IsGranted('CONNECT_DELETE_BY_DATE')]
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
}
