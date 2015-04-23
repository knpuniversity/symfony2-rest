<?php

namespace App\Behat\web;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use AppBundle\Entity\User;
use AppBundle\Entity\Project;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

require_once __DIR__.'/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class WebFeatureContext extends MinkContext
{
    /**
     * @var User
     */
    private $currentUser;

    private $projectContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->projectContext = $environment->getContext('App\Behat\web\ProjectContext');
    }

    /**
     * Deletes the database between each scenario, which causes the tables
     * to be re-created and populated with basic fixtures
     *
     * @BeforeScenario
     */
    public function reloadDatabase()
    {
        $this->getProjectHelper()->reloadDatabase();
    }

    /**
     * @Given /^I click "([^"]*)"$/
     */
    public function iClick($linkName)
    {
        return $this->clickLink($linkName);
    }

    /**
     * @Given /^I select an avatar$/
     *
     * Used on the create programmer page
     *
     */
    public function iSelectAnAvatar()
    {
        $this->getSession()->getPage()->find('css', '.js-selectable-tile li')->click();
    }

    /**
     * @Given /^I click on a project$/
     */
    public function iClickOnAProject()
    {
        $this->getSession()->getPage()->find('css', '.projects-list .js-select-battle-project')->press();
    }


    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->currentUser = $this->getProjectHelper()->createUser('ryan@knplabs.com', 'foo');

        $this->visitPath('/login');
        $this->fillField('Email', 'ryan@knplabs.com');
        $this->fillField('Password', 'foo');
        $this->pressButton('Login');
    }

    /**
     * @Given /^I created the following programmers$/
     */
    public function iCreatedTheFollowingProgrammers(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->getProjectHelper()->createProgrammer($row['nickname'], $this->currentUser);
        }
    }

    /**
     * @Given /^the following projects exist$/
     */
    public function theFollowingProjectsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->getProjectHelper()->createProject($row['name']);
        }
    }

    /**
     * @Given /^someone else created a programmer named "([^"]*)"$/
     */
    public function someoneElseCreatedAProgrammerNamed($nickname)
    {
        $user = $this->getProjectHelper()->createUser('foo'.rand(0, 999).'@bar.com', 'foobar');

        $this->getProjectHelper()->createProgrammer($nickname, $user);
    }

    /**
     * @Then /^I should see (\d+) programmers in the list$/
     */
    public function iShouldSeeProgrammersInTheList($count)
    {
        $programmerList = $this->getSession()
            ->getPage()
            ->findAll('css', '.programmers-list li')
        ;

        assertNotNull($programmerList, 'Cannot see the programmer list');

        assertCount(intval($count), $programmerList);
    }

    /**
     * @Then /^I should see (\d+) projects in the list$/
     */
    public function iShouldSeeProjectsInTheList($count)
    {
        $projectList = $this->getSession()
            ->getPage()
            ->findAll('css', '.projects-list li')
        ;

        assertNotNull($projectList, 'Cannot see the project list');

        assertCount(intval($count), $projectList);
    }

    /**
     * @Given /^the following battles have been valiantly fought:$/
     */
    public function theFollowingBattlesHaveBeenValiantlyFought(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $programmer = $this->getProjectHelper()
                ->getProgrammerRepository()
                ->findOneByNickname($row['programmer']);
            $project = $this->getProjectHelper()
                ->getProjectRepository()
                ->findOneByName($row['project']);

            $this->getProjectHelper()
                ->getBattleManager()
                ->battle($programmer, $project);
        }
    }

    /**
     * @Then /^I should see a table with (\d+) rows$/
     */
    public function iShouldSeeATableWithRows($rowCount)
    {
        $tbl = $this->getSession()->getPage()->find('css', '.main-console-screen table.table');
        assertNotNull($tbl, 'Cannot find a table!');

        assertCount(intval($rowCount), $tbl->findAll('css', 'tbody tr'));
    }

    /**
     * @Then /^I should see a flash message containing "([^"]*)"$/
     */
    public function iShouldSeeAFlashMessageContaining($text)
    {
        return $this->assertElementContainsText('.flash-message', $text);
    }

    /**
     * @Given /^I wait for the dialog to appear$/
     */
    public function iWaitForTheDialogToAppear()
    {
        $this->getSession()->wait(
            5000,
            "jQuery('.modal').is(':visible');"
        );
    }

    /**
     * @Given /^I wait for the dialog to disappear$/
     */
    public function iWaitForTheDialogToDisappear()
    {
        $this->getSession()->wait(
            5000,
            "!jQuery('.modal').is(':visible');"
        );
    }

    /**
     * @Then /^(?:|I )break$/
     */
    public function addABreakpoint()
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {}
        fwrite(STDOUT, "\033[u");

        return;
    }

    /**
     * @return ProjectContext
     */
    private function getProjectHelper()
    {
        return $this->projectContext;
    }
}
