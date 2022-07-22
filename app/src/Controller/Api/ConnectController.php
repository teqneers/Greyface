<?php

namespace App\Controller\Api;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Messenger\Validation;
use DateTimeImmutable;
use IteratorAggregate;
use OutOfBoundsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/greylist')]
class ConnectController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('CONNECT_LIST')]
    public function list(
        Request           $request,
        ConnectRepository $connectRepository
    ): Response
    {
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $list = $connectRepository->findAll($start, $max);

        $count = is_array($list) ? count($list) : $list->count();

        if ($list instanceof IteratorAggregate) {
            $list = $list->getIterator();
        }

        return new JsonResponse([
            'results' => $list,
            'count' => $count,
        ]);
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

        $name = $data['dynamicId']['name'];
        $domain = $data['dynamicId']['domain'];
        $source = $data['dynamicId']['source'];

        $greylist = $connectRepository->find([
            'name' => $name,
            'domain' => $domain,
            'source' => $source
        ]);
        if (!$greylist) {
            throw new OutOfBoundsException(
                'No data set found for Name ' . $name . ', Domain ' . $domain . ' and Source ' . $source
            );
        }
        $connectRepository->delete($greylist);
        return new JsonResponse('Domain deleted successfully!');
    }
}
