## GoBeep - Ecommerce module - Magento 1x

Gobeep’s extension for Magento 1.x is designed to help clients who use the Magento platform to quickly and seamlessly generate links to validate game codes distributed by Gobeep gaming solution.
And to (optionally) display the game on the frontend.

### Installation
First, have your developer download the plugin from GitHub (in the release section). Instructions on how to install the plugin are located in the GitHub repository’s README (this doc). *This code is all you need to have basic functionality working*.

*Note: Looking at `app/code/community/Gobeep_Ecommerce/etc/config.xml`, a developer can see which Magento blocks, helpers and models are used.*

### Setup

After the extension is installed, log in to the Magento Account, Click `System`, and then `Configuration`.
Next, in the `left-nav` menu, under `Sales`, expand the `Gobeep Ecommerce` section.

### Inputs

| Name               | Type             | Description                                                                   |  Default                                | Required |
| ------------------ | ---------------- | ----------------------------------------------------------------------------- | --------------------------------------  | -------- |
| active             | yes/no           | Whether extension is disabled or enabled                                      | No                                      | Yes      |
| game_url           | text             | Game URL (used in `Gobeep_Ecommerce_Block_Game` block)                        |                                         | Yes      |
| cashier_url        | text             | Cashier URL (used in `Gobeep_Ecommerce_Block_Link` block)                     |                                         | Yes      |
| secret             | text             | Secret given by `GoBeep` for signing requests and verify incoming webhooks    |                                         | Yes      |
| from_date          | date             | Start date (Date will be checked to determine if module is enabled or not)    |                                         | No       |
| to_date            | date             | End date (Date will be checked to determine if module is enabled or not)      |                                         | No       |
| eligible_days      | multiselect      | Days of the week when module is enabled                                       |                                         | No       |
| image              | image            | Link image (used in `Gobeep_Ecommerce_Block_Link` block)                      |                                         | Yes*     |
| external_image     | string           | Link image URL (used in `Gobeep_Ecommerce_Block_Link` block)                  |                                         | Yes*     |
| notify             | yes/no           | Whether we should notify users when they are refunded                         |                                         | No       |
| email_template     | string           | Email Notification template (refund)                                          | `gobeep_ecommerce_status_refunded.html` | No       |

<sub>(*) Use one or another</sub>

#### Blocks

##### Link block (cashier link)

We recommend to use the `New Order` email to integrate the cashier link. The `Mage_Sales_Model_Order` object **MUST** be passed to the block.

```{{block type="gobeep_ecommerce/link" order=$order}}```

The default template is located in `app/design/base/default/template/gobeep` directory.

##### Game block

This block should be used to display the game UI on `frontend`, this should be done on `cms_home` block ideally.

```
<reference name="content">
  <block type="gobeep_ecommerce/game" name="gobeep.insterstitial" alias="gobeep_interstitial" after="cms_page"></block>
</reference>
```

The default template is located in `app/design/base/default/template/gobeep` directory.

#### Transactional Email

If you want to use email notifications for refunds, you should create a new template for the `Gobeep Ecommerce Status Refunded` transactional email in the `admin`. 

### Support

For any technical issue with the module, please open an issue on `Github`.

