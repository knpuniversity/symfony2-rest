<?php

namespace AppBundle\Test;

use AppBundle\Entity\Programmer;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Message\AbstractMessage;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\History;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ApiTestCase extends KernelTestCase
{
    private static $staticClient;

    /**
     * @var History
     */
    private static $history;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    private $responseAsserter;

    public static function setUpBeforeClass()
    {
        $baseUrl = getenv('TEST_BASE_URL');
        self::$staticClient = new Client([
            'base_url' => $baseUrl,
            'defaults' => [
                'exceptions' => false
            ]
        ]);
        self::$history = new History();
        self::$staticClient->getEmitter()
            ->attach(self::$history);

        // guaranteeing that /app_test.php is prefixed to all URLs
        self::$staticClient->getEmitter()
            ->on('before', function(BeforeEvent $event) {
                $path = $event->getRequest()->getPath();
                if (strpos($path, '/api') === 0) {
                    $event->getRequest()->setPath('/app_test.php'.$path);
                }
            });

        self::bootKernel();
    }

    protected function setUp()
    {
        $this->client = self::$staticClient;

        $this->purgeDatabase();
    }

    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown()
    {
        // purposefully not calling parent class, which shuts down the kernel
    }

    protected function onNotSuccessfulTest(Exception $e)
    {
        if (self::$history && $lastResponse = self::$history->getLastResponse()) {
            $this->printDebug('');
            $this->printDebug('<error>Failure!</error> when making the following request:');
            $this->printLastRequestUrl();
            $this->printDebug('');

            $this->debugResponse($lastResponse);
        }

        throw $e;
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    protected function printLastRequestUrl()
    {
        $lastRequest = self::$history->getLastRequest();

        if ($lastRequest) {
            $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUrl()));
        } else {
            $this->printDebug('No request was made.');
        }
    }

    protected function debugResponse(ResponseInterface $response)
    {
        $this->printDebug(AbstractMessage::getStartLineAndHeaders($response));
        $body = (string) $response->getBody();

        $contentType = $response->getHeader('Content-Type');
        if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
            $data = json_decode($body);
            if ($data === null) {
                // invalid JSON!
                $this->printDebug($body);
            } else {
                // valid JSON, print it pretty
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            // the response is HTML - see if we should print all of it or some of it
            $isValidHtml = strpos($body, '</body>') !== false;

            if ($isValidHtml) {
                $this->printDebug('');
                $crawler = new Crawler($body);

                // very specific to Symfony's error page
                $isError = $crawler->filter('#traces-0')->count() > 0
                    || strpos($body, 'looks like something went wrong') !== false;
                if ($isError) {
                    $this->printDebug('There was an Error!!!!');
                    $this->printDebug('');
                } else {
                    $this->printDebug('HTML Summary (h1 and h2):');
                }

                // finds the h1 and h2 tags and prints them only
                foreach ($crawler->filter('h1, h2')->extract(array('_text')) as $header) {
                    // avoid these meaningless headers
                    if (strpos($header, 'Stack Trace') !== false) {
                        continue;
                    }
                    if (strpos($header, 'Logs') !== false) {
                        continue;
                    }

                    // remove line breaks so the message looks nice
                    $header = str_replace("\n", ' ', trim($header));
                    // trim any excess whitespace "foo   bar" => "foo bar"
                    $header = preg_replace('/(\s)+/', ' ', $header);

                    if ($isError) {
                        $this->printErrorBlock($header);
                    } else {
                        $this->printDebug($header);
                    }
                }

                $profilerUrl = $response->getHeader('X-Debug-Token-Link');
                if ($profilerUrl) {
                    $fullProfilerUrl = $response->getHeader('Host').$profilerUrl;
                    $this->printDebug('');
                    $this->printDebug(sprintf(
                        'Profiler URL: <comment>%s</comment>',
                        $fullProfilerUrl
                    ));
                }

                // an extra line for spacing
                $this->printDebug('');
            } else {
                $this->printDebug($body);
            }
        }
    }

    /**
     * Print a message out - useful for debugging
     *
     * @param $string
     */
    protected function printDebug($string)
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        $this->output->writeln($string);
    }

    /**
     * Print a debugging message out in a big red block
     *
     * @param $string
     */
    protected function printErrorBlock($string)
    {
        if ($this->formatterHelper === null) {
            $this->formatterHelper = new FormatterHelper();
        }
        $output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);

        $this->printDebug($output);
    }

    protected function createUser($username, $plainPassword = 'foo')
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@foo.com');
        $password = $this->getService('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function createProgrammer(array $data)
    {
        $data = array_merge(array(
            'powerLevel' => rand(0, 10),
            'user' => $this->getEntityManager()
                ->getRepository('AppBundle:User')
                ->findAny()
        ), $data);

        $accessor = PropertyAccess::createPropertyAccessor();
        $programmer = new Programmer();
        foreach ($data as $key => $value) {
            $accessor->setValue($programmer, $key, $value);
        }

        $this->getEntityManager()->persist($programmer);
        $this->getEntityManager()->flush();

        return $programmer;
    }

    /**
     * @return ResponseAsserter
     */
    protected function asserter()
    {
        if ($this->responseAsserter === null) {
            $this->responseAsserter = new ResponseAsserter();
        }

        return $this->responseAsserter;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }
}
