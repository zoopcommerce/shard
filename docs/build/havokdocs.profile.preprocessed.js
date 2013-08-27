var profile = {
    "action": "release",
    "releaseDir": "shard/docs/temp",
    "layerOptimize": "closure",
    "selectorEngine": "lite",
    "mini": 1,
    "buildReportDir": ".",
    "buildReportFilename": "build-report.txt",
    "insertAbsMids": 0,
    "defaultConfig": {
        "hasCache": {
            "dojo-built": 1,
            "dojo-loader": 1,
            "dom": 1,
            "host-browser": 1,
            "config-selectorEngine": "lite"
        },
        "async": true,
        "less": {
            "havok/vendor/bootstrap/less/buttons.less": {
                "rank": 1
            },
            "havok/vendor/bootstrap/less/wells.less": {
                "rank": 1
            },
            "havok/docs/src/less/docs.less": {
                "rank": 4
            },
            "havok/less/havok-defs.less": {
                "defs": true
            },
            "havok/less/havok-rank1.less": {
                "rank": 1
            },
            "havok/less/havok-rank3.less": {
                "rank": 3
            }
        },
        "di": {
            "havok/validator/factory": {
                "gets": {
                    "di": "havok/di/sharedDi!"
                }
            },
            "mystique/Chain": {
                "directives": {
                    "cache": false
                }
            },
            "havok/filter/factory": {
                "gets": {
                    "di": "havok/di/sharedDi!"
                }
            },
            "havok/filter/Chain": {
                "directives": {
                    "cache": false
                }
            },
            "havok/exception/Handler": {
                "gets": {
                    "renderers": [
                        "havok/exception/renderer/Console"
                    ]
                },
                "proxyMethods": [
                    "set",
                    "handle"
                ]
            },
            "havok/router/router": {
                "gets": {
                    "di": "havok/di/sharedDi!"
                },
                "params": {
                    "routes": [
                        {
                            "regex": "[a-zA-Z][a-zA-Z0-9/_-]+",
                            "ignore": true
                        },
                        {
                            "regex": "back",
                            "defaultMethod": -1
                        },
                        {
                            "regex": "forward",
                            "defaultMethod": 1
                        }
                    ]
                }
            },
            "havok/store/manager": {
                "gets": {
                    "stores": "havok/store/stores"
                },
                "proxyMethods": [
                    "get",
                    "getStore"
                ]
            },
            "havok/store/stores": {
                "base": {},
                "proxies": {
                    "font": {
                        "base": "havok/widget/font",
                        "proxyMethods": [
                            "get",
                            "query"
                        ]
                    },
                    "fontsize": {
                        "base": "havok/widget/fontsize",
                        "proxyMethods": [
                            "get",
                            "query"
                        ]
                    }
                }
            }
        }
    },
    "packages": [
        {
            "name": "dojo",
            "location": "dojo"
        },
        {
            "name": "dijit",
            "location": "dijit"
        },
        {
            "name": "havok",
            "location": "havok"
        },
        {
            "name": "mystique",
            "location": "mystique"
        }
    ],
    "staticHasFeatures": {
        "dom": 1,
        "host-browser": 1,
        "dojo-inject-api": 1,
        "dojo-loader-eval-hint-url": 1,
        "dojo-built": 1,
        "host-node": 0,
        "host-rhino": 0,
        "dojo-trace-api": 0,
        "dojo-sync-loader": 0,
        "dojo-config-api": 1,
        "dojo-cdn": 0,
        "dojo-sniff": 0,
        "dojo-requirejs-api": 0,
        "dojo-test-sniff": 0,
        "dojo-combo-api": 0,
        "dojo-undef-api": 0,
        "config-tlmSiblingOfDojo": 0,
        "config-dojo-loader-catches": 0,
        "config-deferredInstrumentation": 0,
        "config-stripStrict": 0,
        "dojo-timeout-api": 0,
        "dojo-dom-ready-api": 0,
        "dojo-log-api": 0,
        "dojo-amd-factory-scan": 0,
        "dojo-publish-privates": 0,
        "dojo-debug-messages": 0
    },
    "plugins": {
        "havok/config/ready": "havok/build/plugin/config/ready",
        "havok/router/baseUrl": "havok/build/plugin/router/baseUrl",
        "havok/router/started": "havok/build/plugin/router/started",
        "havok/di/sharedDi": "havok/build/plugin/di/sharedDi",
        "havok/proxy": "havok/build/plugin/proxy",
        "havok/get": "havok/build/plugin/get",
        "havok/less": "havok/build/plugin/less",
        "dojo/text": "havok/build/plugin/text"
    },
    "transforms": {
        "writeAmd": [
            "havok/build/writeAmd",
            "write"
        ]
    },
    "basePath": "c:/xds/charting/public/dev-assets",
    "localeList": [
        "en-gb",
        "en-us"
    ],
    "layers": {
        "dojo/dojo": {
            "include": [],
            "customBase": 1
        },
        "havok/havokdocs": {
            "includeLocales": [
                "en-gb",
                "en-us"
            ],
            "include": [
                "dojo/parser",
                "havok/widget/NavList",
                "havok/widget/NavBar",
                "havok/widget/NavBarLinks",
                "havok/widget/_AffixMixin",
                "havok/widget/_ScrollSpyMixin"
            ],
            "boot": 1
        }
    }
}