var profile = {
    basePath:"../../../",
    releaseDir:"shard/docs/temp",
    //layerOptimize: 0,
    defaultConfig: {
        merge: [
            'havok/config'
        ],
        less: {
            "havok/vendor/bootstrap/less/buttons.less": {rank: 1},
            "havok/vendor/bootstrap/less/wells.less": {rank: 1},
            "havok/docs/src/less/docs.less": {rank: 4}
        }
    },
    localeList: ['en-gb', 'en-us'],
    layers:{
        "dojo/dojo": {
            include: [],
            customBase: 1
        },
        "havok/havokdocs":{
            includeLocales: ['en-gb', 'en-us'],
            include: [
                'dojo/parser',
                'havok/widget/NavList',
                'havok/widget/NavBar',
                'havok/widget/NavBarLinks',
                'havok/widget/_AffixMixin',
                'havok/widget/_ScrollSpyMixin'
            ],
            boot: 1
        }
    }
}

