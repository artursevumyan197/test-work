<?php

namespace App\Controller;

use App\Entity\CreatedDateUrl;
use App\Entity\Url;
use App\Repository\UrlRepository;
use App\Service\UrlStatisticsService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{

    private $urlRepository;

    public function __construct(UrlRepository $urlRepository)
    {
        $this->urlRepository = $urlRepository;
    }

    /**
     * @Route("/encode-url", name="encode_url", methods={"POST"})
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $inputUrl = $request->get('url');
        $parsedUrl = parse_url($inputUrl);
        $domain = $parsedUrl['host'];
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $existingUrl = $urlRepository->findOneByUrl($inputUrl);

        if (!empty($existingUrl)) {
            return $this->json([
                'hash' => $existingUrl->getHash()
            ]);
        }

        $url = new Url();
        $url->setUrl($inputUrl);
        $url->setDomain($domain);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($url);
        $entityManager->flush();

        return $this->json([
            'hash' => $url->getHash()
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url", methods={"GET"})
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));

        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }

        $createdDate = $url->getCreatedDate();
        $lifetime = new \DateInterval('PT1M');

        if ($createdDate->add($lifetime) < new \DateTimeImmutable()) {
            return $this->json(['error' => 'The URL has expired.']);
        }
        return $this->json([
            'url' => $url->getUrl()
        ]);
    }

    /**
     * @Route("/gourl", name="go_url", methods={"GET"}
     */
    public function goUrl(Request $request): RedirectResponse
    {
        $this->urlRepository = $this->getDoctrine()->getRepository(Url::class);

        $url = $this->urlRepository->findOneByHash($request->get('hash'));

        if (empty($url)) {
            throw $this->createNotFoundException('The hash does not exist');
        }

        return $this->redirect($url->getUrl());
    }

    /**
     * @Route("/store-created-date-url", name="store-created-date-url")
     */
    public function storeCreatedDateUrl(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $createDateUrl = new CreatedDateUrl();
        $createDateUrl->setUrl($data['url']);
        $createDateUrl->setCreatedDate($data['created_date']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($createDateUrl);
        $entityManager->flush();

        return $this->json([
            'message' => 'Data stored successfully.',
        ]);
    }

    /**
     * @Route("/api/url-statistics", name="url_statistics", methods={"GET"})
     * @throws NoResultException|NonUniqueResultException
     */
    public function getUrlStatistics(UrlStatisticsService $urlStatisticsService, Request $request): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $domain = $request->get('domain');

        if ($startDate && $endDate) {
            $startDate = new \DateTimeImmutable($startDate);
            $endDate = new \DateTimeImmutable($endDate);
            $uniqueUrlsInPeriod = $urlStatisticsService->countUniqueUrlsInPeriod($startDate, $endDate);
        }

        if ($domain) {
            $uniqueUrlsWithDomain = $urlStatisticsService->countUniqueUrlsWithDomain($domain);
        }

        $statistics = $urlStatisticsService->calculateStatistics();

        return $this->json([
            'статистика' => $statistics,
            'number of unique urls for a given period of time' => $uniqueUrlsInPeriod ?? null,
            'number of unique urls with the specified domain' => $uniqueUrlsWithDomain ?? null,
        ]);
    }
}
