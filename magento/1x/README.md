## GoBeep - Ecommerce module - Magento 1x

Gobeep’s extension for Magento 1.x is designed to help clients who use the Magento platform to quickly and seamlessly generate links to validate game codes distributed by Gobeep gaming solution.

### Installation
First, have your developer download the plugin from GitHub (in the release section). Instructions on how to install the plugin are located in the GitHub repository’s README (this doc). *This code is all you need to have basic functionality working*.

:info: Note: Looking at `app/code/community/Gobeep_Ecommerce/etc/config.xml`, a developer can see what Magento blocks, helpers and models are used.

### Setup

After the extension is installed, log in to the Magento Account, Click `System`, and then `Configuration`.

Next, in the `left-nav` menu, under `Sales`, expand the `Gobeep Ecommerce` section.

* Set Enabled to `Yes`*
* Enter your `Private Key`*
* Enter `Start date/End date`, block won't return any output if current date is not in range, if you don't specify any date, the plugin will always display the block output.
* Upload internal image or specify external image*

The private key is given by GoBeep 

#### Transactional email integration

We recommend to use the `New Order` email to integrate the plugin. The `Mage_Sales_Model_Order` object **MUST** be passed to the block.

```{{block type="gobeep_ecommerce/link" order=$order}}```

The template is located in `app/design/base/default/template/gobeep` directory.

### Support

For any technical issue with the module, please open an issue on `Github`.

