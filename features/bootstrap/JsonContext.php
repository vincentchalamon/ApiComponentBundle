<?php

/*
 * This file is part of the Silverback API Component Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\JsonContext as BaseJsonContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\Json\Json;
use Behatch\Json\JsonSchema;
use PHPUnit\Framework\Assert;

final class JsonContext extends BaseJsonContext
{
    public array $components = [];
    private RestContext $restContext;

    public function __construct(HttpCallResultPool $httpCallResultPool)
    {
        parent::__construct($httpCallResultPool);
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase(BeforeScenarioScope $scope): void
    {
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
    }

    /**
     * @Then /^the JSON should be deep equal to:$/
     */
    public function theJsonShouldBeDeepEqualTo(PyStringNode $content)
    {
        $actual = $this->getJson();
        try {
            $expected = new Json($content);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        $actual = new Json(json_encode($this->sortArrays($actual->getContent())));
        $expected = new Json(json_encode($this->sortArrays($expected->getContent())));

        $this->assertSame(
            (string) $expected,
            (string) $actual,
            "The json is equal to:\n" . $actual->encode()
        );
    }

    /**
     * @Then /^the JSON should be a superset of:$/
     */
    public function theJsonIsASupersetOf(PyStringNode $content)
    {
        $actual = json_decode($this->httpCallResultPool->getResult()->getValue(), true);
        Assert::assertArraySubset(json_decode($content->getRaw(), true), $actual);
    }

    private function sortArrays($obj)
    {
        $isObject = is_object($obj);

        foreach ($obj as $key => $value) {
            if (null === $value || is_scalar($value)) {
                continue;
            }

            if (is_array($value)) {
                sort($value);
            }

            $value = $this->sortArrays($value);

            $isObject ? $obj->{$key} = $value : $obj[$key] = $value;
        }

        return $obj;
    }

    /**
     * @Then the JSON should be valid according to the schema file :file
     */
    public function theJsonShouldBeValidAccordingToTheSchemaFile(string $file)
    {
        try {
            $this->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([file_get_contents(__DIR__ . '/../schema/' . $file)], 1));
        } catch (\Exception $exception) {
            $actual = $this->getJson();
            throw new \Exception($exception->getMessage() . "\n\nThe json is equal to:\n" . $actual->encode());
        }
    }

    /**
     * @Then the JSON should be an array with each entry valid according to the schema file :file
     */
    public function theJsonShouldBeAnArrayWithEachEntryValidAccordingToTheSchemaFile(string $file)
    {
        $json = $this->getJson();
        $schema = new PyStringNode([file_get_contents(__DIR__ . '/../schema/' . $file)], 1);
        try {
            foreach ($json as $item) {
                $this->inspector->validate(
                    $item,
                    new JsonSchema($schema)
                );
            }
        } catch (\Exception $exception) {
            $actual = $this->getJson();
            throw new \Exception($exception->getMessage() . "\n\nThe json is equal to:\n" . $actual->encode());
        }
    }

//    /**
//     * @Then I save the response IRI as :name
//     */
//    public function iSaveTheResponseIriAs($name)
//    {
//        $json = $this->getJson();
//        $this->restContext->components[$name] = $json->getContent()->{'@id'};
//    }
}
