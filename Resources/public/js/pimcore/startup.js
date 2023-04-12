pimcore.registerNS("pimcore.plugin.BlackbitPimcoreBigcommerceSyncBundle");

pimcore.plugin.BlackbitPimcoreBigcommerceSyncBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.BlackbitPimcoreBigcommerceSyncBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("BlackbitPimcoreBigcommerceSyncBundle ready!");
    }
});

var BlackbitPimcoreBigcommerceSyncBundlePlugin = new pimcore.plugin.BlackbitPimcoreBigcommerceSyncBundle();
