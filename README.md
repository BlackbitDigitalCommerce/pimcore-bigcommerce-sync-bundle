# Blackbit Pimcore Bigcommerce Synchronize Bundle

This bundle simplifies the connection and synchronization between BigCommerce and Pimcore + DataDirector. BlackbitPimcore BigcommerceSyncBundle automates the process of managing and syncing products, product variants, product categories, brands and more.

The bundle supports the import of the following entities:

## Requirements

Blackbit Pimcore Bigcommerce Synchronize Bundle requires the installation of the [Blackbit Data Director Bundle](https://bitbucket.org/blackbitwerbung/pimcore-bigcommerce-sync-bundle/). 

## Installation

### Composer

To get the plugin code you have to [buy the plugin](https://shop.blackbit.com/) or write an email to [info@blackbit.de](mailto:info@blackbit.de).

You then either get access to the bundle's [Bitbucket repository](https://bitbucket.org/blackbitwerbung/pimcore-bigcommerce-sync-bundle) or you get the plugin code as a zip file. Accessing the Bitbucket repository has the advantage that you will always see changes to the plugin in the pull requests and are able to update to a new version yourself - please visit [this page](https://shop.blackbit.com/bitbucket-access-to-blackbit-plugin-development/) if this sounds interesting to you - if it does, please send us the email address of your BitBucket account so we can allow access to the repository.

When we allow your account to access our repository, please add the repository to the `composer.json` in your Pimcore root folder (see [Composer repositories](https://getcomposer.org/doc/05-repositories.md#vcs)):

```json
"repositories": {
    "pimcore-bigcommerce-sync": {
        "type": "vcs",
        "url": "git@bitbucket.org:blackbitwerbung/pimcore-bigcommerce-sync-bundle"
    }
}
```

(Please [add your public SSH key to your Bitbucket account](https://support.atlassian.com/bitbucket-cloud/docs/add-access-keys/#Step-3.-Add-the-public-key-to-your-repository) for this to work)

Alternatively if you received the plugin code as zip file, please upload the zip file to your server - e.g. create a folder `bundles` in the Pimcore root folder) and add the following to your `composer.json`:

```json
"repositories": [
    {
        "type": "artifact",
        "url": "./bundles/"
    }
]
```

Beware that when you put the zip directly in the Pimcore root folder, and add `"url": "./"` it will still work but Composer will scan *all* files under the Pimcore root recursively to find bundle zip files (incl. assets, versions etc) - which will take quite a long time.

Then you should be able to execute `composer require blackbit/pimcore-bigcommerce-sync` (or `composer update blackbit/pimcore-bigcommerce-sync --with-dependencies` for updates if you already have this bundle installed) from CLI.

At last you have to enable and install the plugin, either via browser UI or via CLI `bin/console pimcore:bundle:enable BlackbitPimcoreBigcommerceSyncBundle && bin/console pimcore:bundle:install BlackbitPimcoreBigcommerceSyncBundle`

You can always access the latest version by executing `composer update blackbit/pimcore-bigcommerce-sync --with-dependencies` on CLI.


## Quick overview

The bundle supports the import of the following entities:
 - Store info;
 - Brand;
 - Category;
 - Channels;
 - Product; 
   - Variant; 
   - Bulk Pricing Rule; 
   - Custom Field; 
   - Option; 
   - Image; 
   - Video.

Also, the prepared bundle is already prepared and [automatic](#automatic-export) export is configured for:
- Brand;
- Category;
- Channels;
- Product;
  - Variant;
  - Bulk Pricing Rule;
  - Custom Field;
  - Option;
  - Image;
  - Video.

*{#automatic-export}*Automatic* - after you have made any changes to a product or its dependencies and clicked the **Save** or **Save and Publish** buttons, this product will be updated in BigCommerce with the new values automatically.

## How to import data to objects

Import can be divided into several stages. **The sequence is important!**

1. [Import Stores](#1-import-stores);
2. [Import Categories](#2-import-categories);
3. [Import Brands](#3-import-brands);
4. [Import Products](#4-import-products);

### 1. Import Stores
1. Before starting the import, you need to create the `stores` folder in Data Objects and create an object of the `Store` type with the name (`key`) of your BigCommerce store in it. 
2. Then, in this created object, fill in the **Bigcommerce ID** and **Token** fields. Your store in BigCommerce. These settings can be found in the admin panel of your BigCommerce store.
3. The next step is to change the default settings to work with your store.
   In the list of Data Director dataports, you will find **Bigcommerce Import Store Info EN** dataport. You can rename it to whatever you want. Here you need to specify the name (`key`) of your store, which you created earlier. In **Import source (file, folder, URL, cURL command, PHP script)** adjust `sandbox` on store yours name(`key`) and save new settings.
4. After the import is successfully completed, you will see that most of the fields in the new store object are filled in, and you can proceed to the next step of importing data from BigCommerce to Pimcore.

If you have several BigCommerce stores, then you can create the necessary `Store` date objects for each of them and configure them by cloning the dataports in the above way.
In order to implement support for multiple stores in Pimcore, the multilingual and localization functionality is used. Localization corresponds to the value of the **Language** field of your store.
All catalog data objects also have localization and will accordingly use the localization of a particular store.

After the `Store` has been imported, you can start importing the catalog.

### 2. Import Categories
1. Open **Bigcommerce Import Category EN** dataport and in **Import source (file, folder, URL, cURL command, PHP script)** adjust `sandbox` on store yours name(`key`) and save new settings.
2. On the Attribute Mapping tab, find the `Store` attribute and edit the callback function by changing the `sandbox` to your store.
3. Run import.

_**If you use several storages you have to clone `Bigcommerce Import Category EN`, rename it, for example `Bigcommerce Import Category DE`, change store `key` in **Import source (file, folder, URL, cURL command, PHP script)**, and reassign `Raw Data Fields` on required language in Attribute Mapping tab. Also do not forget execute step #2. 
That will need to do Import, Brands, Products and dependent from Product entities.**_

### 3. Import Brands
1. Open **Bigcommerce Import Brand EN** dataport andIn **Import source (file, folder, URL, cURL command, PHP script)** adjust `sandbox` on store yours name(`key`) and save new settings.
2. On the Attribute Mapping tab, find the `Store` attribute and edit the callback function by changing the `sandbox` to your store.
3. Run import.

### 4. Import Products
1. Open **Bigcommerce Import Product EN** dataport andIn **Import source (file, folder, URL, cURL command, PHP script)** adjust `sandbox` on store yours name(`key`) and save new settings.
2. On the Attribute Mapping tab, find the `Store` attribute and edit the callback function by changing the `sandbox` to your store.
3. You don't need to make 1 and 2 items for Product dependent dataports, because they receive store key and token from parent dataport. Only needs to reassign `Raw Data Fields` on required language in Attribute Mapping tab. Also dependent dataports from **Bigcommerce Import Product EN** no need to start manually, they are running by **Bigcommerce Import Product EN** automatically.
4. Run import.

## How to emport data to objects

Exporting catalog entities such as `Categories`, `Brands`, `Products` and dependent Product entities is both complex and simple.

First you should do the next simple settings:
1. Setup **Main Domain** in System Settings -> Website;
2. [Import your Stores](#1-import-stores). 

Catalog entities are exported automatically using the **Data Director** function **Incremental Export**. That is, when you change the value in the field of any Date Object and save it, it will be exported to BigCommerce automatically and almost instantly.

Also, if a dependent Date Object has been modified, for example you have changed one of the product images, that product along with its dependencies (including the edited Image Date Object) will be automatically updated in BigCommerce.