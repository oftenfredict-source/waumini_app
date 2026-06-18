<?php

return [
  'templates' => [
    'leader_appointment' => [
      'label' => 'Leader Appointment',
      'description' => 'Sent when a member is appointed to a leadership position.',
      'placeholders' => ['{{name}}', '{{position}}', '{{church_name}}'],
      'default' => "Hongera {{name}}! Umechaguliwa rasmi kuwa {{position}} wa kanisa la {{church_name}}.\n\nMungu akupe hekima, ujasiri na neema katika kutimiza wajibu huu wa kiroho.",
    ],
    'member_credentials' => [
      'label' => 'Member Account Credentials',
      'description' => 'Sent when a new member account is created (if enabled in settings).',
      'placeholders' => ['{{name}}', '{{church_name}}', '{{member_id}}', '{{password}}'],
      'default' => "Karibu {{name}}!\nAkaunti yako ya {{church_name}} imeundwa.\nMember ID: {{member_id}}\nNenosiri: {{password}}",
    ],
    'password_reset' => [
      'label' => 'Password Reset',
      'description' => 'Sent when a staff user password is reset.',
      'placeholders' => ['{{name}}', '{{church_name}}', '{{login_id}}', '{{password}}'],
      'default' => "Habari {{name}}!\nNenosiri jipya la akaunti yako ya {{church_name}}:\nIngia kwa: {{login_id}}\nNenosiri: {{password}}",
    ],
    'login_otp' => [
      'label' => 'Login OTP',
      'description' => 'Sent for SMS one-time password login verification.',
      'placeholders' => ['{{name}}', '{{church_name}}', '{{otp_code}}'],
      'default' => "Shalom {{name}}, nambari yako ya kuthibitisha kuingia kwenye {{church_name}} ni: {{otp_code}}\nHalali kwa dakika 5. Usishiriki na mtu yeyote.",
    ],
    'promise_guest_reminder' => [
      'label' => 'Promise Guest — Reminder',
      'description' => 'Reminder for a guest who promised to attend a service or event.',
      'placeholders' => ['{{name}}', '{{church_name}}', '{{event_name}}', '{{date}}', '{{details}}'],
      'default' => "Shalom {{name}}, tunakukumbusha kuhusu ahadi yako ya kuhudhuria {{event_name}} tarehe {{date}} katika {{church_name}}.{{details}}\n\nKaribu sana! Mungu akubariki.",
    ],
    'promise_guest_welcome_back' => [
      'label' => 'Promise Guest — Welcome Back',
      'description' => 'Sent to a guest who already attended, inviting them to the next service.',
      'placeholders' => ['{{name}}', '{{church_name}}', '{{event_name}}', '{{date}}', '{{details}}'],
      'default' => "Shalom {{name}}, karibu tena katika {{church_name}}! Tunafurahi ulituhudumia. Karibu tena {{event_name}} tarehe {{date}}.{{details}}\n\nMungu akubariki.",
    ],
    'promise_guest_welcome_back_generic' => [
      'label' => 'Promise Guest — Welcome Back (No Upcoming Service)',
      'description' => 'Sent when a returning guest has no scheduled upcoming service.',
      'placeholders' => ['{{name}}', '{{church_name}}'],
      'default' => "Shalom {{name}}, karibu tena katika {{church_name}}! Tunafurahi ulituhudumia. Karibu tena pamoja nasi.\n\nMungu akubariki.",
    ],
    'finance_approval' => [
      'label' => 'Finance Approval',
      'description' => 'Sent when tithe, offering, or pledge payment is approved.',
      'placeholders' => ['{{name}}', '{{payment_type}}', '{{amount}}', '{{date}}'],
      'default' => "Hongera {{name}}! {{payment_type}} yako ya TZS {{amount}} tarehe {{date}} imethibitishwa na imepokelewa kikamilifu.\nAsante kwa mchango wako wa kiroho. Mungu akubariki!",
    ],
    'announcement' => [
      'label' => 'Announcement',
      'description' => 'Sent when an announcement is published with SMS enabled.',
      'placeholders' => ['{{church_name}}', '{{title}}', '{{content}}'],
      'default' => "{{church_name}}: {{title}}\n{{content}}",
    ],
    'manual' => [
      'label' => 'Manual / Custom SMS',
      'description' => 'Default template for manually composed messages from the SMS store.',
      'placeholders' => ['{{church_name}}'],
      'default' => "{{church_name}}: ",
    ],
  ],

  'context_labels' => [
    'general' => 'General',
    'leader_appointment' => 'Leader Appointment',
    'member_credentials' => 'Member Credentials',
    'password_reset' => 'Password Reset',
    'login_otp' => 'Login OTP',
    'promise_guest' => 'Promise Guest',
    'promise_guest_reminder' => 'Promise Guest Reminder',
    'promise_guest_welcome_back' => 'Promise Guest Welcome Back',
    'promise_guest_welcome_back_generic' => 'Promise Guest Welcome Back',
    'finance_approval' => 'Finance Approval',
    'announcement' => 'Announcement',
    'manual' => 'Manual',
  ],
];
