<?php

namespace App\Command;

use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SendNewUrlsCommand extends Command
{
    protected static $defaultName = 'app:send-new-urls';
    private $urlRepository;
    private $em;
    private $params;

    public function __construct(UrlRepository $urlRepository, EntityManagerInterface $em, ParameterBagInterface $params)
    {
        $this->urlRepository = $urlRepository;
        $this->em = $em;
        $this->params = $params;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send newly created Url entities to the specified endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $httpClient = HttpClient::create();
        $endpoint = $this->params->get('url_endpoint');

        $urls = $this->urlRepository->findBy(['sent' => false]);

        foreach ($urls as $url) {

            try {
                $response = $httpClient->request('POST', $endpoint, [
                    'json' => [
                        'url' => $url->getUrl(),
                        'created_date' => $url->getCreatedDate()->format('Y-m-d H:i:s'),
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $url->markAsSent();
                    $this->em->flush();
                }

            } catch (TransportExceptionInterface $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
        }

        $io->success('Newly created Url entities have been sent.');

        return Command::SUCCESS;
    }
}