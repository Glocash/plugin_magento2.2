# Glocash Magento2 plugin

This extension is for Magento 2.2.x

### Quickstart

#####Install

1. Upload module content to the path below magento2 :  /magento2/app/code/

2. In command line, navigate to the magento2 root folder
Enter the following commands:

```
php bin/magento module:enable Glocash_Checkout
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

If the page prompts: "There has been an error processing your request", run the following:
```
php bin/magento setup:static-content:deploy
```

The plugin is now installed

#####Setup

1. Log into the Magento Admin
if you are unable to get access to your admin 
2. Go to *Stores* / *Configuration*
3. Go to *Sales* / *Payment Methods*
4. Find the Glocash Settings, enter the Email and Secret Key
5. Enable the desired payment methods and set allowed countries
6. Save the settings
