<?php

namespace Zoop\Shard\Test\Dojo;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;

class DojoTest extends BaseTest {

    protected $generator;

    protected $path;

    public function setUp(){

        $this->path = __DIR__ . '/../../../../Dojo';

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.dojo' => [
                    'flat_file_strategy' => 'save',
                    'file_paths' => [[
                        'filter' => '',
                        'path' => $this->path
                    ]]
                ],
                'extension.generator' => [
                    'resource_map' => [
                        'shard/simple.js' => [
                            'generator' => 'generator.dojo.model',
                            'class'     => __NAMESPACE__ . '\TestAsset\Document\Simple'
                        ],
                        'shard/simple/NameInput.js' => [
                            'generator'       => 'generator.dojo.input',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                            'options'         => [
                                'field'       => 'name',
                                'params'      => [
                                    'label'       => 'NAME',
                                    'tooltip'     => 'The document name',
                                    'description' => 'This is a longer description'
                                ]
                            ]
                        ],
                        'shard/simple/Form.js' => [
                            'generator'       => 'generator.dojo.form',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                        ],
                        'shard/simple/Store.js' => [
                            'generator'       => 'generator.dojo.jsonrest',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                        ],
                        'shard/simple/ModelValidator.js' => [
                            'generator'       => 'generator.dojo.modelvalidator',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                        ],
                        'shard/simple/MultiFieldValidator.js' => [
                            'generator'       => 'generator.dojo.multifieldvalidator',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                        ],
                        'shard/simple/NameValidator.js' => [
                            'generator'       => 'generator.dojo.validator',
                            'class'           => __NAMESPACE__ . '\TestAsset\Document\Simple',
                            'options'         => [
                                'field'       => 'name',
                            ]
                        ]
                    ]
                ]
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                ]
            ]
       ]);

       $this->resourceMap = $manifest->getServiceManager()->get('resourceMap');
    }

    public function testInputGenerator(){
        $this->resourceMap->get('shard/simple/NameInput.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/NameInput.js'),
            file_get_contents($this->path . '/shard/simple/NameInput.js')
        );
    }

    public function testFormGenerator(){
        $this->resourceMap->get('shard/simple/Form.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/Form.js'),
            file_get_contents($this->path . '/shard/simple/Form.js')
        );
    }

    public function testModelGenerator(){
        $this->resourceMap->get('shard/simple.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple.js'),
            file_get_contents($this->path . '/shard/simple.js')
        );
    }

    public function testJsonRestGenerator(){
        $this->resourceMap->get('shard/simple/Store.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/Store.js'),
            file_get_contents($this->path . '/shard/simple/Store.js')
        );
    }

    public function testModelValidatorGenerator(){
        $this->resourceMap->get('shard/simple/ModelValidator.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/ModelValidator.js'),
            file_get_contents($this->path . '/shard/simple/ModelValidator.js')
        );
    }

    public function testMultiFieldValidatorGenerator(){
        $this->resourceMap->get('shard/simple/MultiFieldValidator.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/MultiFieldValidator.js'),
            file_get_contents($this->path . '/shard/simple/MultiFieldValidator.js')
        );
    }

    public function testValidatorGenerator(){
        $this->resourceMap->get('shard/simple/NameValidator.js');
        $this->assertEquals(
            file_get_contents(__DIR__ . '/TestAsset/Simple/NameValidator.js'),
            file_get_contents($this->path . '/shard/simple/NameValidator.js')
        );
    }
}