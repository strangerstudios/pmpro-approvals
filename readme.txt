=== Paid Memberships Pro - Approvals ===
Contributors: strangerstudios, andrewza
Tags: paid memberships pro, pmpro, approval, approvals, workflow
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.6.5

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
1. To customize the emails related to this plugin, navigate to Memberships > Settings > Email Templates in the WordPress admin.

View full documentation at: https://www.paidmembershipspro.com/add-ons/approval-process-membership/

== Changelog ==
= 1.6.5 - 2025-07-25 =
* ENHANCEMENT: Added "Extra Fields" to the admin approval emails. This will include any user fields that are shown on checkout. (@andrewlimaza)
* ENHANCEMENT: Added support for sending a test email within the Email Templates editor screen. This requires Paid Memberships Pro v3.5 or later. (@andrewlimaza)
* BUG FIX: Fixed an issue where the default recipient would always show "member" for admin emails (@dwanjuki)
* BUG FIX: Adjusted the membership account approval status to use the CSS class "pmpro_list_item" to match styling for Paid Memberships Pro V3.0+ (@andrewlimaza)


= 1.6.4 - 2025-04-15 =
* BUG FIX: Fixed an error when viewing the Approvals settings page without any level configured for approval. (@dwanjuki)
* BUG FIX: Fixed an issue when a member changed their membership level and the approval meta was not correctly cleared. (@dwanjuki)

= 1.6.3 - 2025-03-06 =
* BUG FIX: Fixed an issue where the "Require Membership" checkbox would not consider a user's approval status. #201 (@dparker1005)
* BUG FIX: Fixed an issue where the "Membership Pending Approval" message may not overwrite the default message when content is restricted. #201 (@dparker1005)

= 1.6.2 - 2025-03-03 =
* ENHANCEMENT: Now using the new `PMPro_Email_Template` class to show email template variables when editing email templates in PMPro v3.4+. #199 (@MaximilianoRicoTabo)
* BUG FIX: Fixed an issue where the `[membership]` shortcode would not restrict correctly when specific levels were not specified. #194 (@MaximilianoRicoTabo)
* BUG FIX: Fixed a PHP warning that showed when calculating the number of pending approvals in the dashboard. #200 (@MaximilianoRicoTabo)
* BUG FIX: Fixed a PHP warning that showed when the approval status was checked for a level that does not exist. #195 (@ipokkel)
* BUG FIX: Fixed a PHP warning that showed when displaying a confirmation message for a level that does not exist. #196 (@MaximilianoRicoTabo)
* DEPRECATED: Marked the `pmpro_member_shortcode_access()` method as deprecated in favor of `pmpro_has_membership_level()`. #194 (@MaximilianoRicoTabo)

= 1.6.1 - 2024-09-19 =
* BUG FIX/ENHANCEMENT: Fixes conflict with Pay By Check if user fields are restricted by level. #190 (@dparker1005)
* BUG FIX: Fixed PHP warnings when the admin email address is not associated with a WordPress account. #188 (@dwanjuki)

= 1.6 - 2024-07-18 =
* ENHANCEMENT: Updated the frontend UI for compatibility with PMPro v3.1. #186 (@dparker1005)
* ENHANCEMENT: Added a new filter `pmpro_approvals_show_level_confirmation_message` to allow showing the level confirmation message within the approval confirmation message. #183 (@andrewlimaza)

= 1.5.1 - 2023-11-30 =
* BUG FIX/ENHANCEMENT: Added support for User Fields instead of Register Helper Fields when previewing the pending member information. Please upgrade any Register Helper code to use the User Fields logic or class.
* REFACTOR: Refactored pmpro_getOption functions to use get_option instead.

= 1.5 - 2022-10-06 =
* ENHANCEMENT: Added support for Multiple Memberships Per User, allowing individual approval for each level ID a user may hold.
* ENHANCEMENT: Improved logic for the Approval Counter to increase accuracy.
* ENHANCEMENT: Added support for member directory filtering within the PMPro BuddyPress & BuddyBoss Add On.
* BUG FIX: Fixed an issue where free checkouts would cause a warning that level ID's are missing on the confirmation page.

= 1.4.3 - 2022-07-19 =
* ENHANCEMENT: Added Approval level template and support for PMPro v2.9+ settings UI.

= 1.4.2 - 2022-02-21 =
* ENHANCEMENT: Capture username of admin/person who reset approval status of a user when logging this action. (@ideadude)
* BUG FIX: Fix issue in some cases where 'pmproap_user_is_approved' filter would not run when filtering via code snippets. (@ideadude)

= 1.4.1 - 2021-09-22 =
* ENHANCEMENT: Added $level parameter to the 'pmpro_approvals_member_denied_email_data' filter.
* BUG FIX: Fixed an issue where pending members were automatically approved when checking out for the same level multiple times.
* BUG FIX: Fixed an issue where Register Helper Fields that show up for certain levels would cause other custom fields to be hidden from the view info of the Approvals page.
* BUG FIX: Fixed issues where approved members would see content for non-members shortcode - [membership level="0"].

= 1.4 - 2021-04-07 =
* SECURITY: General escaping and sanitizing when outputting data on the user's approvals information page.
* ENHANCEMENT: Added translation file for German locale.
* ENHANCEMENT: Added in filter 'pmpro_approvals_approval_count_sql_parts' and 'pmpro_approvals_approval_count_sql' to make SQL query (when counting pending users) easier to manipulate.
* ENHANCEMENT: Added in filter 'pmpro_approvals_pending_approvals_sql_parts' and 'pmpro_approvals_pending_approvals_sql' to make SQL query (for retrieving pending users) easier to manipulate.
* ENHANCEMENT: Added in filter 'pmpro_approvals_level_restrict_checkout' to allow bypassing of checkout restriction. Thanks @edwinbsmith
* ENHANCEMENT: Improved coding readability and variable naming.
* ENHANCEMENT: Make links clickable when custom fields are added to the user's profile and previewing approval information. Note: the stored value requires http:// or https:// to make it clickable.
* ENHANCEMENT: Integrate with Pay By Check. When a user is approved, approve their pending check order. Has to be enabled by using the filter `pmpro_approvals_pbc_success_on_approval` and returning `true` to enable it.
* BUG FIX/ENHANCEMENT: Clear pending approval data if the user changes their level or cancels before being approved or denied.
* BUG FIX: Allow pending/non-approved members to cancel their membership level on the frontend.
* BUG FIX: Fixes an issue for [membership] shortcode that didn't pass levels attribute and pending members used to gain access to restricted content.
* BUG FIX: Fixed an issue where refreshing the approvals page may resend the approval email - this now only gets sent once.
* BUG FIX: Fixed an issue where multiple fields belonging to different levels with the same name would show up twice on the view info page of the approvals. This now supports the 'level' attribute inside Register Helper when displaying fields.
* BUG FIX: Fixed general issues when Paid Memberships Pro was not active.
* BUG FIX: Fixed warnings of missing variables when approval emails were sent.
* BUG FIX: Fixed an issue where an undefined variable was used inside the approval's list within the WordPress admin.
* BUG FIX: Fixed general warnings when a level does not require payment.
* BUG FIX: Support Pay By Check confirmation message/instructions when a level uses both Pay By Check and requires approval.
* BUG FIX: Fixing a notice for check payment instructions confirmation message text when approval level is free.

= 1.3.4 - 2019-11-13 =
* ENHANCEMENT: Improved query for Approval Count inside dashboard for speed improvements to reduce load times while in WordPress dashboard.

= 1.3.3 - 2019-10-31 =
* BUG FIX: Fixed issues with PMPro Member Directory integration if your DB prefix was not wp_. (Thanks, Ciprian Tepes)
* ENHANCEMENT: Added pmpro_approvals_level_requires_approval to filter the result of the PMPro_Approvals::requiresApproval() method.

= 1.3.2 - 2019-08-22 =
* BUG FIX: get_current_screen threw a fatal error in some cases.

= 1.3.1 - 2019-08-12 =
* BUG FIX: User approval status was not showing in the members list.
* BUG FIX: Edit user page was not showing custom fields from Register Helper.
* BUG FIX: Approval links not showing under members list and was hidden completely.

= 1.3 - 2019-07-23 =
* SECURITY: Improved escaping when outputting data to the screen.
* BUG FIX: Remove "Status" from account page if the user's level doesn't require approval.
* BUG FIX: Approval link being escaped in Paid Memberships Pro 2.x+ 'recent' members dashboard widget.
* BUG FIX/ENHANCEMENT: Improved custom fields not showing for pending members edit page and view info pages.
* BUG FIX/ENHANCEMENT: Integrate with Email Confirmation Add On. Improved UX.
* ENHANCEMENT: Improved i18n. Some strings were missing. Please submit a PR to get your locale included in a future release.
* ENHANCEMENT: Integrate with Member Directory Add On. Automatically hide non-approved users from Member Directory Add On pages. Member Directory .5.4+ required.
* ENHANCEMENT: Hooks added in for Approvals table to allow inserting custom columns ("pmpro_approvals_list_extra_cols_header" and "pmpro_approvals_list_extra_cols_body" respectively).

= 1.2 - 2019-03-20 =
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
