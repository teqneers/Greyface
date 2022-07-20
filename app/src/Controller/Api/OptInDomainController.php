<?php

namespace App\Controller\Api;

use App\Domain\Entity\OptInDomain\OptInDomainRepository;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Command\CreateUser;
use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Command\UpdateUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/optin-domains')]
class OptInDomainController
{

    #[Route('', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function list(
        Request               $request,
        OptInDomainRepository $optInDomainRepository
    ): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $start = $request->query->get('start');
        $max = $request->query->get('max') ?? 20;
        $domains = $optInDomainRepository->findAll(false, $start, $max);
        dump($domains);
        $data = [];
        foreach ($domains as $domain) {
            dump($domain);
            $data[] = [
                'domain' => $domain->getDomain(),
            ];
        }
        $response->setContent(json_encode([
            'results' => $data,
            'count' => is_array($domains) ? count($domains) : $domains->count(),
        ]));
        return $response;
    }
}
