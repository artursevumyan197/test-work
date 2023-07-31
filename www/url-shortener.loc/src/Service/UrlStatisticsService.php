<?php

namespace App\Service;

use App\Repository\UrlRepository;
use DateTimeInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UrlStatisticsService
{
    private $urlRepository;

    public function __construct(UrlRepository  $urlRepository)
    {
         $this->urlRepository = $urlRepository;
    }

    /**
     * Calculate statistics for all URLs.
     *
     * @return array The total number of URLs and the last created URL.
     */
    public function calculateStatistics(): array
    {
        $urls = $this->urlRepository->findAll();

        if (empty($urls)) {

            return [
                'total_urls' => 0,
                'last_created_url' => null,
            ];
        }
        $totalUrls = count($urls);
        $oldestUrlDate = end($urls)->getCreatedDate();

        return [
            'total_urls' => $totalUrls,
            'last_created_url' => $oldestUrlDate->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Count unique URLs in a specific period.
     *
     * @param DateTimeInterface $startDate The start date of the period.
     * @param DateTimeInterface $endDate The end date of the period.
     * @return int The number of unique URLs.
     * @throws NoResultException|NonUniqueResultException
     */
    public function countUniqueUrlsInPeriod(DateTimeInterface $startDate, DateTimeInterface $endDate): int
    {
        return $this->urlRepository->countUniqueUrlsInPeriod($startDate, $endDate);
    }

    /**
     * Count unique URLs with a specific domain.
     * @param string $domain The domain to count URLs for.
     * @return int The number of unique URLs.
     * @throws NoResultException|NonUniqueResultException
     */
    public function countUniqueUrlsWithDomain(string $domain): int
    {
        return $this->urlRepository->countUniqueUrlsWithDomain($domain);
    }
}