<?php

namespace App\Behat\web;

use AppBundle\Entity\User;
use AppBundle\Entity\Programmer;
use Behat\Gherkin\Node\TableNode;
use AppBundle\Entity\ApiToken;
use AppBundle\Entity\Project;
use Behat\Behat\Context\SnippetAcceptingContext;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Sub-context for interacting with our project
 */
class ProjectContext implements SnippetAcceptingContext
{
    /**
     * @var \AppKernel
     */
    private static $kernel;

    private $lastBattle;

    /**
     * @Given /^the user "([^"]*)" exists$/
     */
    public function theUserExists($username)
    {
        $this->thereIsAUserWithPassword($username, 'foo');
    }

    /**
     * @Given /^there is a user "([^"]*)" with password "([^"]*)"$/
     */
    public function thereIsAUserWithPassword($username, $password)
    {
        $this->createUser($username.'@foo.com', $password, $username);
    }

    /**
     * @Given /^the following programmers exist:$/
     */
    public function theFollowingProgrammersExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $nickname = $row['nickname'];
            unset($row['nickname']);

            $this->createProgrammer($nickname, null, $row);
        }
    }

    /**
     * @Given /^there is a programmer called "([^"]*)"$/
     */
    public function thereIsAProgrammerCalled($name)
    {
        $this->createProgrammer($name);
    }

    /**
     * @Given /^"([^"]*)" has an authentication token "([^"]*)"$/
     */
    public function hasAnAuthenticationToken($username, $tokenString)
    {
        $user = $this->getUserRepository()->findUserByUsername($username);
        if (!$user) {
            throw new \Exception(sprintf('Cannot find user '.$username));
        }

        $token = new ApiToken($user->getId());
        $token->setNotes('Behat testing!');
        $token->setToken($tokenString);

        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^there is a project called "([^"]*)"$/
     */
    public function createProject($name)
    {
        $project = new Project();
        $project->setName($name);
        $project->setDifficultyLevel(rand(1, 10));

        $this->getEntityManager()->persist($project);
        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^there has been a battle between "([^"]*)" and "([^"]*)"$/
     */
    public function thereHasBeenABattleBetweenAnd($programmerName, $projectName)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($programmerName);
        $project = $this->getProjectRepository()->findOneByName($projectName);

        $this->lastBattle = $this->getBattleManager()->battle($programmer, $project);
    }

    /**
     * Clears all the data!
     */
    public function reloadDatabase()
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    /**
     * @BeforeSuite
     */
    public static function bootstrapApp()
    {
        // required so that we get the doctrine annotations stuff from that file loaded in
        require __DIR__ . '/../../app/autoload.php';
        require __DIR__ . '/../../app/AppKernel.php';

        self::$kernel = new \AppKernel('test', true);
        self::$kernel->boot();
    }

    public function getService($id)
    {
        return self::$kernel->getContainer()->get($id);
    }

    public function createUser($email, $plainPassword, $username = null)
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username ? $username : 'John'.rand(0, 10000));

        $password = $this->getService('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function createProgrammer($nickname, User $owner = null, array $data = array())
    {
        $avatarNumber = isset($data['avatarNumber']) ? $data['avatarNumber'] : rand(1, 6);
        $programmer = new Programmer($nickname, $avatarNumber);

        $data = array_merge(array(
            'powerLevel' => rand(0, 10),
        ), $data);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $prop => $val) {
            $propertyAccessor->setValue($programmer, $prop, $val);
        }

        if (!$owner) {
            $owner = $this->getUserRepository()->findAny();
        }
        $programmer->setUser($owner);

        $this->getEntityManager()->persist($programmer);
        $this->getEntityManager()->flush();

        return $programmer;
    }

    /**
     * @return \AppBundle\Battle\BattleManager
     */
    public function getBattleManager()
    {
        return $this->getService('battle.battle_manager');
    }

    /**
     * @return \AppBundle\Repository\ProgrammerRepository
     */
    public function getProgrammerRepository()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:Programmer');
    }

    /**
     * @return \AppBundle\Repository\ProjectRepository
     */
    public function getProjectRepository()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:Project');
    }

    /**
     * @return \AppBundle\Repository\UserRepository
     */
    public function getUserRepository()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:User');
    }

    /**
     * @return \AppBundle\Repository\ApiTokenRepository
     */
    public function getApiTokenRepository()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:ApiToken');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getService('doctrine')
            ->getManager();
    }

}