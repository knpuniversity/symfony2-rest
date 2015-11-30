<?php

namespace AppBundle\Test;

use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Helper class to assert different conditions on Guzzle responses
 */
class ResponseAsserter extends \PHPUnit_Framework_Assert
{
    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * Asserts the array of property names are in the JSON response
     *
     * @param ResponseInterface $response
     * @param array $expectedProperties
     * @throws \Exception
     */
    public function assertResponsePropertiesExist(ResponseInterface $response, array $expectedProperties)
    {
        foreach ($expectedProperties as $propertyPath) {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);
        }
    }

    /**
     * Asserts the specific propertyPath is in the JSON response
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyExists(ResponseInterface $response, $propertyPath)
    {
        // this will blow up if the property doesn't exist
        $this->readResponseProperty($response, $propertyPath);
    }

    /**
     * Asserts the given property path does *not* exist
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyDoesNotExist(ResponseInterface $response, $propertyPath)
    {
        try {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);

            $this->fail(sprintf('Property "%s" exists, but it should not', $propertyPath));
        } catch (RuntimeException $e) {
            // cool, it blew up
            // this catches all errors (but only errors) from the PropertyAccess component
        }
    }

    /**
     * Asserts the response JSON property equals the given value
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed $expectedValue
     * @throws \Exception
     */
    public function assertResponsePropertyEquals(ResponseInterface $response, $propertyPath, $expectedValue)
    {
        $actual = $this->readResponseProperty($response, $propertyPath);
        $this->assertEquals(
            $expectedValue,
            $actual,
            sprintf(
                'Property "%s": Expected "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actual, true)
            )
        );
    }

    /**
     * Asserts the response property is an array
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyIsArray(ResponseInterface $response, $propertyPath)
    {
        $this->assertInternalType('array', $this->readResponseProperty($response, $propertyPath));
    }

    /**
     * Asserts the given response property (probably an array) has the expected "count"
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param integer $expectedCount
     * @throws \Exception
     */
    public function assertResponsePropertyCount(ResponseInterface $response, $propertyPath, $expectedCount)
    {
        $this->assertCount((int)$expectedCount, $this->readResponseProperty($response, $propertyPath));
    }

    /**
     * Asserts the specific response property contains the given value
     *
     * e.g. "Hello world!" contains "world"
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed $expectedValue
     * @throws \Exception
     */
    public function assertResponsePropertyContains(ResponseInterface $response, $propertyPath, $expectedValue)
    {
        $actualPropertyValue = $this->readResponseProperty($response, $propertyPath);
        $this->assertContains(
            $expectedValue,
            $actualPropertyValue,
            sprintf(
                'Property "%s": Expected to contain "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actualPropertyValue, true)
            )
        );
    }

    /**
     * Reads a JSON response property and returns the value
     *
     * This will explode if the value does not exist
     *
     * @param ResponseInterface $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @return mixed
     * @throws \Exception
     */
    public function readResponseProperty(ResponseInterface $response, $propertyPath)
    {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }

        $data = json_decode((string)$response->getBody());

        if ($data === null) {
            throw new \Exception(sprintf(
                'Cannot read property "%s" - the response is invalid (is it HTML?)',
                $propertyPath
            ));
        }

        try {
            return $this->accessor->getValue($data, $propertyPath);
        } catch (AccessException $e) {
            // it could be a stdClass or an array of stdClass
            $values = is_array($data) ? $data : get_object_vars($data);

            throw new AccessException(sprintf(
                'Error reading property "%s" from available keys (%s)',
                $propertyPath,
                implode(', ', array_keys($values))
            ), 0, $e);
        }
    }
}
