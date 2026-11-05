<?php

return [
    'button' => 'Help',
    'button_title' => 'Open page user manual',
    'modal_title' => 'User Manual',
    'how_to' => 'How to use this page',
    'tips' => 'Tips',
    'view_all' => 'Browse all topics',
    'close' => 'Close',
    'fallback_title' => 'Page help',
    'index_title' => 'User Manual',
    'index_subtitle' => 'Guides for every section of the system',
    'search_placeholder' => 'Search topics...',
    'no_results' => 'No matching topics found.',
    'open_topic' => 'Read guide',

    'topics' => [
        'default' => [
            'title' => 'Getting started',
            'summary' => 'Use the sidebar to open modules. The Help button on each page explains that screen.',
            'steps' => [
                'Open a module from the left menu.',
                'Use filters and search to find records.',
                'Click Help anytime for page-specific steps.',
            ],
            'tips' => [
                'Your permissions control which menus you can see.',
            ],
        ],
        'church_default' => [
            'title' => 'Church portal',
            'summary' => 'Manage members, services, finance, and settings for your church.',
            'steps' => [
                'Start from Dashboard for an overview.',
                'Use Members to register and update people.',
                'Record tithes, offerings, and attendance regularly.',
            ],
            'tips' => [
                'Switch language from the top bar if needed.',
            ],
        ],
        'owner_default' => [
            'title' => 'Owner portal',
            'summary' => 'Manage churches, billing, demos, and platform settings.',
            'steps' => [
                'Review the owner dashboard for platform health.',
                'Approve demo requests and activate subscriptions.',
                'Record payments and invoices when churches pay.',
            ],
            'tips' => [
                'Installation fees cover year 1; annual fees start after that year.',
            ],
        ],

        'dashboard' => [
            'title' => 'Church dashboard',
            'summary' => 'Overview of members, attendance, and income for your church.',
            'steps' => [
                'Check the welcome banner for today’s date and your role.',
                'Review the statistic cards for members, attendance, and income.',
                'Use quick links or the sidebar to open detailed modules.',
            ],
            'tips' => [
                'Pending finance approvals appear as an alert when you can approve.',
            ],
        ],
        'members' => [
            'title' => 'Members list',
            'summary' => 'Search, filter, and open member profiles.',
            'steps' => [
                'Use search and filters to narrow the list.',
                'Click a member to view their full profile.',
                'Use Register Member to start a new registration.',
            ],
            'tips' => [
                'Archived members are listed under Archived Members.',
            ],
        ],
        'members_register' => [
            'title' => 'Register member',
            'summary' => 'Complete the 5-step form to add a new member.',
            'steps' => [
                'Follow steps 1–5: personal, contact, church, family, then review.',
                'Required fields are marked with *.',
                'Share the self-registration link if the member should apply themselves.',
                'Save at the end to create the member record.',
            ],
            'tips' => [
                'You can leave the wizard and return later only after saving; finish all steps when possible.',
            ],
        ],
        'members_edit' => [
            'title' => 'Edit member',
            'summary' => 'Update an existing member’s details using the same wizard steps.',
            'steps' => [
                'Change only the fields that need updating.',
                'Move through the steps and save when finished.',
            ],
            'tips' => [
                'Keep envelope numbers unique when required by age settings.',
            ],
        ],
        'members_show' => [
            'title' => 'Member profile',
            'summary' => 'View member details, family, and related records.',
            'steps' => [
                'Review personal and church information.',
                'Use Edit to update details.',
                'Open related actions such as certificates when available.',
            ],
            'tips' => [
                'Archive instead of delete when a member leaves.',
            ],
        ],
        'members_archived' => [
            'title' => 'Archived members',
            'summary' => 'Members who were archived and can be restored if needed.',
            'steps' => [
                'Find the member in the archived list.',
                'Restore them if they return to active membership.',
            ],
            'tips' => [
                'Archiving keeps history for reports and giving.',
            ],
        ],
        'members_children' => [
            'title' => 'Children / dependants',
            'summary' => 'Manage children linked to members.',
            'steps' => [
                'Open a parent member or the children list.',
                'Add or edit child details.',
                'Convert a child to a full member when they come of age.',
            ],
            'tips' => [
                'Link children to the correct parent for family records.',
            ],
        ],
        'member_registrations' => [
            'title' => 'Registration approvals',
            'summary' => 'Review and approve self-registration applications.',
            'steps' => [
                'Open a pending application.',
                'Verify the details submitted by the applicant.',
                'Approve to create a member, or reject with a reason.',
            ],
            'tips' => [
                'Share the public register link from the Register Member page.',
            ],
        ],
        'member_requests' => [
            'title' => 'Member requests',
            'summary' => 'Handle certificate and other member service requests.',
            'steps' => [
                'Open a request from the list.',
                'Respond, approve, or provide the document.',
            ],
            'tips' => [
                'Members submit requests from their member portal.',
            ],
        ],
        'branches' => [
            'title' => 'Branches',
            'summary' => 'Manage church branches when multi-branch is enabled.',
            'steps' => [
                'Add a branch with name and code.',
                'Use the branch switcher in the header to focus on one branch.',
                'Exit branch view to see all-church data again.',
            ],
            'tips' => [
                'Branch context filters members, finance, and reports.',
            ],
        ],
        'leadership' => [
            'title' => 'Leadership',
            'summary' => 'Assign and track church leaders.',
            'steps' => [
                'View current leaders.',
                'Assign leadership by selecting a member and role.',
                'Deactivate a leader when their term ends.',
            ],
            'tips' => [
                'Leaders may appear in the member portal leaders list.',
            ],
        ],
        'departments' => [
            'title' => 'Departments',
            'summary' => 'Organize ministries and department membership.',
            'steps' => [
                'Create a department.',
                'Assign members to departments.',
                'Update department details as needed.',
            ],
            'tips' => [
                'Departments help target announcements and reports.',
            ],
        ],
        'announcements' => [
            'title' => 'Announcements',
            'summary' => 'Publish messages to all members or selected groups.',
            'steps' => [
                'Create an announcement with title and content.',
                'Choose the audience (all, members, etc.).',
                'Save to publish.',
            ],
            'tips' => [
                'Members can read announcements in the member portal.',
            ],
        ],
        'services' => [
            'title' => 'Church services',
            'summary' => 'Plan and manage regular church services.',
            'steps' => [
                'Create a service with date and details.',
                'Link attendance and related records as needed.',
            ],
            'tips' => [
                'Keep service names consistent for clearer reports.',
            ],
        ],
        'special_events' => [
            'title' => 'Special events',
            'summary' => 'Manage one-off church events.',
            'steps' => [
                'Create the event with date and description.',
                'Update or close it after it happens.',
            ],
            'tips' => [
                'Use announcements to promote upcoming events.',
            ],
        ],
        'celebrations' => [
            'title' => 'Celebrations',
            'summary' => 'Track birthdays, anniversaries, and similar celebrations.',
            'steps' => [
                'Add a celebration linked to a member when required.',
                'Review upcoming celebrations from the list.',
            ],
            'tips' => [
                'Useful for pastoral follow-up and SMS greetings.',
            ],
        ],
        'promise_guests' => [
            'title' => 'Promise / temp guests',
            'summary' => 'Record temporary guests and follow-ups.',
            'steps' => [
                'Add a guest with contact details.',
                'Send SMS when available.',
                'Update status after follow-up.',
            ],
            'tips' => [
                'Convert interested guests into member registrations.',
            ],
        ],
        'attendance' => [
            'title' => 'Attendance',
            'summary' => 'Record who attended services and events.',
            'steps' => [
                'Open attendance for a service or create a new session.',
                'Check in members manually or via QR when enabled.',
                'Save the attendance record.',
            ],
            'tips' => [
                'Monthly attendance totals appear on the dashboard.',
            ],
        ],
        'bereavements' => [
            'title' => 'Bereavements',
            'summary' => 'Track bereavement events and contributions.',
            'steps' => [
                'Create a bereavement record.',
                'Add contributions as they are received.',
                'Update status when the process completes.',
            ],
            'tips' => [
                'Keep contribution methods accurate for finance reports.',
            ],
        ],
        'finance_dashboard' => [
            'title' => 'Finance dashboard',
            'summary' => 'Summary of giving, expenses, and pending approvals.',
            'steps' => [
                'Review income and expense summaries.',
                'Open Tithes, Offerings, or Budget for details.',
            ],
            'tips' => [
                'Approvers should clear pending items regularly.',
            ],
        ],
        'finance_approvals' => [
            'title' => 'Finance approvals',
            'summary' => 'Approve or reject pending finance transactions.',
            'steps' => [
                'Open a pending item.',
                'Verify amount and details.',
                'Approve or reject with a note if needed.',
            ],
            'tips' => [
                'Only users with finance.approve can use this page.',
            ],
        ],
        'tithes' => [
            'title' => 'Tithes',
            'summary' => 'Record member tithe payments.',
            'steps' => [
                'Create a tithe entry for a member.',
                'Enter amount, date, and payment method.',
                'Save; it may require approval depending on settings.',
            ],
            'tips' => [
                'Bulk entry is available when enabled on the form.',
            ],
        ],
        'offerings' => [
            'title' => 'Offerings',
            'summary' => 'Record offerings and contributions.',
            'steps' => [
                'Choose member-linked or general offering.',
                'Enter amount and method.',
                'Save the record.',
            ],
            'tips' => [
                'Use clear notes for special offerings.',
            ],
        ],
        'pledges' => [
            'title' => 'Pledges',
            'summary' => 'Track pledges and payments toward them.',
            'steps' => [
                'Create a pledge with amount and schedule.',
                'Record payments as they come in.',
                'Monitor remaining balance on the pledge.',
            ],
            'tips' => [
                'Pledge payments may go through approval.',
            ],
        ],
        'budget' => [
            'title' => 'Budget & expenses',
            'summary' => 'Plan budgets and record church expenses.',
            'steps' => [
                'Create or open a budget period.',
                'Add expenses against budget lines.',
                'Compare planned vs actual spending.',
            ],
            'tips' => [
                'Keep categories consistent for better reports.',
            ],
        ],
        'assets' => [
            'title' => 'Church assets',
            'summary' => 'Inventory of church property and equipment.',
            'steps' => [
                'Add an asset with name, value, and condition.',
                'Update status when assets move or are disposed.',
            ],
            'tips' => [
                'Attach notes for warranty or location details.',
            ],
        ],
        'analytics' => [
            'title' => 'Analytics',
            'summary' => 'Charts and trends for church activity.',
            'steps' => [
                'Open Analytics from the menu.',
                'Review trends for membership, giving, or attendance.',
            ],
            'tips' => [
                'Use date filters when available for clearer insight.',
            ],
        ],
        'reports' => [
            'title' => 'Reports',
            'summary' => 'Generate printable and downloadable church reports.',
            'steps' => [
                'Choose a report type from the list.',
                'Set filters such as date range.',
                'View, print, or export the result.',
            ],
            'tips' => [
                'Run reports after data entry is up to date.',
            ],
        ],
        'system' => [
            'title' => 'System management',
            'summary' => 'Administrative tools for users, settings, and SMS.',
            'steps' => [
                'Open System Management from the sidebar.',
                'Pick settings, users, roles, SMS, or support.',
            ],
            'tips' => [
                'Only church admins and permitted staff should change system settings.',
            ],
        ],
        'system_settings' => [
            'title' => 'System settings',
            'summary' => 'Configure church preferences, notifications, and security.',
            'steps' => [
                'Open each settings tab you need.',
                'Update values and save the tab.',
            ],
            'tips' => [
                'Test SMS after changing notification settings.',
            ],
        ],
        'system_users' => [
            'title' => 'Manage users',
            'summary' => 'Create and manage church staff login accounts.',
            'steps' => [
                'Add a user with email and role.',
                'Reset password when someone is locked out.',
                'Deactivate accounts that should no longer access the system.',
            ],
            'tips' => [
                'Assign the least privilege needed for each role.',
            ],
        ],
        'system_roles' => [
            'title' => 'Roles & permissions',
            'summary' => 'Control what each staff role can do.',
            'steps' => [
                'Open Roles & Permissions.',
                'Tick permissions for each role.',
                'Save changes.',
            ],
            'tips' => [
                'Review permissions after adding new modules.',
            ],
        ],
        'system_sms' => [
            'title' => 'SMS store',
            'summary' => 'Templates, message history, and manual SMS send.',
            'steps' => [
                'Edit templates under the Templates tab.',
                'Review sent/failed messages under Messages.',
                'Use Compose to send a manual SMS when allowed.',
            ],
            'tips' => [
                'SMS must be enabled in notifications settings.',
            ],
        ],
        'system_subscription' => [
            'title' => 'Upgrade plan',
            'summary' => 'View your package and request plan changes.',
            'steps' => [
                'Check your current plan and renewal/trial dates.',
                'Compare packages.',
                'Contact platform support or pay as instructed to upgrade.',
            ],
            'tips' => [
                'Installation covers year 1; annual fees start after that year.',
            ],
        ],
        'system_support' => [
            'title' => 'Support tickets',
            'summary' => 'Ask the platform team for help.',
            'steps' => [
                'Click New Ticket.',
                'Choose category and priority, then describe the issue.',
                'Submit and track status on this page.',
            ],
            'tips' => [
                'Include steps to reproduce for technical issues.',
            ],
        ],
        'system_logs' => [
            'title' => 'System logs',
            'summary' => 'Audit trail of important system actions.',
            'steps' => [
                'Filter by date or action if available.',
                'Review entries for troubleshooting.',
            ],
            'tips' => [
                'Logs are read-only.',
            ],
        ],
        'system_sessions' => [
            'title' => 'User sessions',
            'summary' => 'See active logins and revoke sessions.',
            'steps' => [
                'Review active sessions.',
                'Revoke a session if a device should be signed out.',
            ],
            'tips' => [
                'Useful if a user lost a device.',
            ],
        ],
        'system_monitor' => [
            'title' => 'System monitor',
            'summary' => 'Health and monitoring information for the church system.',
            'steps' => [
                'Open the monitor page.',
                'Check status indicators.',
            ],
            'tips' => [
                'Contact support if something stays unhealthy.',
            ],
        ],
        'system_otps' => [
            'title' => 'OTP management',
            'summary' => 'View one-time passwords used for login verification.',
            'steps' => [
                'Open OTP Management.',
                'Review recent OTP activity if troubleshooting login.',
            ],
            'tips' => [
                'Do not share OTP codes with others.',
            ],
        ],

        'member_portal' => [
            'title' => 'Member portal',
            'summary' => 'Self-service area for registered members.',
            'steps' => [
                'Update your profile.',
                'Read announcements and submit requests.',
            ],
            'tips' => [
                'Contact church staff if your account is missing information.',
            ],
        ],
        'member_portal_dashboard' => [
            'title' => 'My dashboard',
            'summary' => 'Your personal overview in the member portal.',
            'steps' => [
                'Review your membership summary.',
                'Open profile, announcements, or requests from the menu.',
            ],
            'tips' => [
                'Keep your phone and email up to date.',
            ],
        ],
        'member_portal_profile' => [
            'title' => 'My profile',
            'summary' => 'Update your personal contact details.',
            'steps' => [
                'Edit the fields you need to change.',
                'Save your profile.',
            ],
            'tips' => [
                'Some fields may only be changed by church staff.',
            ],
        ],
        'member_portal_announcements' => [
            'title' => 'My announcements',
            'summary' => 'Read church announcements sent to you.',
            'steps' => [
                'Open an announcement to read the full message.',
            ],
            'tips' => [
                'Check regularly for service updates.',
            ],
        ],
        'member_portal_requests' => [
            'title' => 'My requests',
            'summary' => 'Submit and track certificate or service requests.',
            'steps' => [
                'Create a new request.',
                'Track status until the church responds.',
            ],
            'tips' => [
                'Attach clear details so staff can process faster.',
            ],
        ],
        'member_portal_services' => [
            'title' => 'My services',
            'summary' => 'See upcoming church services.',
            'steps' => [
                'Browse the services list.',
                'Note dates and times for attendance.',
            ],
            'tips' => [
                'Ask staff if a service seems missing.',
            ],
        ],
        'member_portal_leaders' => [
            'title' => 'Church leaders',
            'summary' => 'View current church leadership.',
            'steps' => [
                'Browse the leaders list.',
            ],
            'tips' => [
                'Contact details appear only if the church publishes them.',
            ],
        ],

        'owner_dashboard' => [
            'title' => 'Owner dashboard',
            'summary' => 'Platform overview: churches, trials, and revenue.',
            'steps' => [
                'Review church status counts.',
                'Follow up on trials and suspended churches.',
                'Open Payments or Churches for detailed work.',
            ],
            'tips' => [
                'Demo requests need approve/reject action.',
            ],
        ],
        'owner_churches' => [
            'title' => 'Churches',
            'summary' => 'Create and manage church tenants.',
            'steps' => [
                'Add a church and admin login.',
                'Assign trial or activate paid subscription.',
                'Suspend, activate, or impersonate when needed.',
            ],
            'tips' => [
                'Record installation payment to cover the first year.',
            ],
        ],
        'owner_demo_requests' => [
            'title' => 'Demo requests',
            'summary' => 'Approve or reject churches requesting a trial.',
            'steps' => [
                'Open the request list.',
                'Approve to create a trial church, or reject.',
            ],
            'tips' => [
                'Approval can notify the church by SMS when configured.',
            ],
        ],
        'owner_subscriptions' => [
            'title' => 'Subscriptions',
            'summary' => 'Packages and church subscription status.',
            'steps' => [
                'Review packages and pricing.',
                'Check which churches are on trial or active.',
            ],
            'tips' => [
                'Edit packages under Settings → Packages.',
            ],
        ],
        'owner_payments' => [
            'title' => 'Payments',
            'summary' => 'Track and record church payments manually.',
            'steps' => [
                'Click Record Manual Payment when a church pays.',
                'Choose installation (year 1) or annual fee (after year 1).',
                'Optionally link a pending invoice.',
            ],
            'tips' => [
                'Yearly/installation payments activate expired subscriptions for 1 year.',
            ],
        ],
        'owner_invoices' => [
            'title' => 'Invoices',
            'summary' => 'Generate and mark invoices as paid.',
            'steps' => [
                'Generate an installation or annual invoice.',
                'Share or download the PDF.',
                'Mark as paid when payment is received.',
            ],
            'tips' => [
                'Pending invoices show as outstanding debt on Payments.',
            ],
        ],
        'owner_revenue' => [
            'title' => 'Revenue',
            'summary' => 'Revenue charts and collected amounts.',
            'steps' => [
                'Review monthly and yearly collected revenue.',
                'Compare package performance.',
            ],
            'tips' => [
                'Figures come from completed payments.',
            ],
        ],
        'owner_sms_usage' => [
            'title' => 'SMS usage',
            'summary' => 'Monitor SMS consumption by church.',
            'steps' => [
                'Open a church SMS usage page.',
                'Review monthly usage totals.',
            ],
            'tips' => [
                'SMS depends on platform and church notification settings.',
            ],
        ],
        'owner_users' => [
            'title' => 'Users & roles',
            'summary' => 'Platform and church user counts.',
            'steps' => [
                'Review staff and member counts by church.',
                'Open a church for more detail.',
            ],
            'tips' => [
                'Church staff accounts are managed inside each church.',
            ],
        ],
        'owner_support' => [
            'title' => 'Support tickets',
            'summary' => 'Respond to church support requests.',
            'steps' => [
                'Open a ticket.',
                'Update status and priority as you work.',
                'Mark resolved or closed when done.',
            ],
            'tips' => [
                'Churches create tickets under System → Support Tickets.',
            ],
        ],
        'owner_settings' => [
            'title' => 'Platform settings',
            'summary' => 'Configure billing, SMS, legal text, and packages.',
            'steps' => [
                'Open each settings tab.',
                'Save changes carefully.',
                'Test SMS after gateway changes.',
            ],
            'tips' => [
                'Support email receives contact and ticket notifications.',
            ],
        ],
        'owner' => [
            'title' => 'Owner panel',
            'summary' => 'Platform administration for Waumini Link.',
            'steps' => [
                'Use the sidebar to open churches, billing, and settings.',
                'Click Help on any page for that page’s guide.',
            ],
            'tips' => [
                'Use impersonation only for legitimate support.',
            ],
        ],
    ],
];
