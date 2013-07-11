<?php

namespace Zoop\Shard\Test\Dojo\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\Validator\Chain({
 *     @Shard\Validator(class = "Zoop/Shard/Test/ClassValidator1"),
 *     @Shard\Validator(class = "Zoop/Shard/Test/ClassValidator2", options = {"option1" = "a", "option2" = "b"})
 * })
 * @Shard\Serializer\ClassName
 */
class Simple {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator(class = "Zoop/Shard/Test/NameValidator1"),
     *     @Shard\Validator(class = "Zoop/Shard/Test/NameValidator2", options = {"option1" = "b", "option2" = "b"})
     * })
     */
    protected $name;

    /**
     * @ODM\String
     * @Shard\Validator(class = "Zoop/Shard/Test/CountryValidator1")
     */
    protected $country;

    /**
     * @ODM\String
     */
    protected $camelCaseField;

    /**
     * @ODM\String
     * @Shard\Serializer\Ignore
     * @Shard\Validator\NotRequired
     */
    protected $ignoreField;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = (string) $name;
    }

    public function getCountry() {
        return $this->country;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    public function getCamelCaseField() {
        return $this->camelCaseField;
    }

    public function setCamelCaseField($camelCaseField) {
        $this->camelCaseField = $camelCaseField;
    }

    public function getIgnoreField() {
        return $this->ignoreField;
    }

    public function setIgnoreField($ignoreField) {
        $this->ignoreField = $ignoreField;
    }
}
