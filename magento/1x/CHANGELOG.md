## 1.1.2.0 (February 14, 2020)

MISC:
* Remove useless observer

CHORE:
* Added `CONTRIBUTING.md` and `CHANGELOG.md`

## 1.1.1.0 (February 14, 2020)

BUG FIXES:
* Fix order existence check
* Fix date format in system/config/date


## 1.1.0.0 (February 12, 2020)

NEW FEATURES:
* Webhook support
* Added new fields in system/config
  * `game_url`: meant to store the Gobeep game url
  * `eligible_days`: meant to configure eligible days for the activation of the module
  * `notify`: meant to activate refund notifications
  * `email_template`: meant to choose the email template for refund notifications
* Added install script for new table
* Added default template for new `Game` block (to display popover on frontend)
* New menu in `sales/` to view Gobeep refunds in `admin`