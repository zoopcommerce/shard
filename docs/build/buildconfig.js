// Dojo Configuration
dojoConfig = {
    async: true,
    baseUrl: "../",
    packages: [
        {
            name: "dojo",
            location: "dojo"
        },
        {
            name: "dijit",
            location: "dijit"
        },
        {
            name:'util',
            location:'util'
        },
        {
            name:'build',
            location:'util/build'
        },
        {
            name:'doh',
            location:'util/doh'
        },
        {
            name: "havok",
            location: "havok"
        }
    ]
};

require('../../dojo/dojo');
