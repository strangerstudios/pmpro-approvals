=== Paid Memberships Pro - Approvals ===
Contributors: strangerstudios, andrewza
Tags: paid memberships pro, pmpro, approval, approvals, workflow
Requires at least: 4.0
Tested up to: 5.1.1
Stable tag: 1.2

Grants administrators the ability to approve/deny memberships after signup.

== Description ==

Set up a unique approval or application process for your membership site. After a member signs up, the Admin, Membership Manager, or new Approver roles will have the ability to approve their membership or deny the application. The member will be charged the initial payment and subscription configured (if applicable) based on the level’s settings at checkout.

Additionally, you can set a level to require an approved membership from another level in order to complete checkout. This allows you to offer a two-step membership application and full membership registration model. You can place an application fee on the application level, and then charge your full recurring membership fee on the primary membership for approved members.

The applying member and the admin will receive email notifications along the process to alert them of the application status.

Members pending approval will not have access to view members-only content until their membership has been approved. After a member is approved, they will be able to access all members-only content.

== Installation ==

= Prerequisites =
1. You must have Paid Memberships Pro installed and activated on your site.

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /pmpro-approvals/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.

= How to Use =
1. To set a level's approval requirements, edit your membership level and adjust the settings under "Approval Settings".
1. To process membership approvals, navigate to the Approvals Dashboard under Memberships > Approvals.

View full documentation at: https://www.paidmembershipspro.com/add-ons/approval-process-membership/

== Changelog ==
= 1.2 =
* BUG FIX: Fixed issue with [membership] shortcode not working correctly for pending/logged-in non-members.
* BUG FIX: Fixed integration with Email Confirmation. Admins will only be able to approve/deny users once their emails are confirmed.
* BUG FIX: Fixed integration with Email Templates Admin Editor sending out wrong usernames. Dear admin, for users and vice versa.
* BUG FIX: Fixed pending member notification bubble for PMPro 2.0+.
* BUG FIX: Fixed menus for PMPro 2.0+. (Thanks, ioamnesia on GitHub)
* BUG FIX/ENHANCEMENT: Reworked the email functionality entirely.
* ENHANCEMENT: Added i18n support for Approvals with French translation files. Please submit your translation files via a Pull Request or on www.paidmembershipspro.com so we may include these in future a release.
* ENHANCEMENT: Filters added for approved, denied and pending status.
* ENHANCEMENT: Support Register Helper fields inside the "View" profile of pending users. Including "File" fields.

= 1.1 =
* ENHANCEMENT: Added action hooks: pmpro_approvals_before_approve_member, pmpro_approvals_after_approve_member, pmpro_approvals_before_deny_member, pmpro_approvals_after_deny_member, pmpro_approvals_before_reset_member, pmpro_approvals_after_reset_member. All of these hooks pass two parameters: $user_id, $level_id.

= 1.0.4 =
* BUG FIX: Fixed issue where approvals weren't showing up as pending due to corrupted settings.

= 1.0.3 =
* BUG FIX: Added the PMPro_Approvals::hasMembershipLevelSansApproval($level_id, $user_id) function, which is used now and fixes issues where a user was "approved" for a level they didn't even have.

= 1.0.2 =
* BUG FIX: Fixed warning when checking if a level is hidden.
* BUG/ENHANCEMENT: If you set a level to require another level's approval, that level will automatically be set to require approval.
* ENHANCEMENT: Showing a count of pending approvals in the menu now.

= 1.0.1 =
* BUG FIX: The Approval Settings on the edit membership level page now allow you to choose the first Yes option to require approval for this specific level.

= 1.0 =
* Initial version.

== Upgrade Notice ==
= 1.2 =
* Please update to the latest version of Approvals.
