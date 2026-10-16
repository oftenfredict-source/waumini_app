# WauminiLink Mobile API

REST API ya Flutter / mobile app. Inatumia **backend na database ileile** ya web (Laravel + MySQL). Haitengenezi tables mpya za members au users.

Auth: **Laravel Sanctum** (Bearer token). Web login (session) haibadiliki.

---

## Base URL

Chagua URL kulingana na jinsi unavyoendesha Laravel:

| Mazingira | Base URL |
|---|---|
| `php artisan serve` | `http://127.0.0.1:8000/api` |
| XAMPP (folder `waumini_link`) | `http://localhost/waumini_link/public/api` |
| Production | `https://your-domain.com/api` |

Kwenye **Android emulator**, `localhost` ya PC ni `http://10.0.2.2:8000/api`.  
Kwenye **simu halisi**, tumia IP ya kompyuta kwenye Wi‑Fi, mfano `http://192.168.1.20:8000/api`.

---

## Headers

Kila request:

```http
Accept: application/json
Content-Type: application/json
```

Protected endpoints:

```http
Authorization: Bearer {token}
```

---

## Authentication

Login ni **sawa na church portal ya web**:

- Identifier: **email** au **member number** (namba ya mshirika)
- Si simu (`phone`) — web haitumii phone kama login
- Password ni ileile ya akaunti ya web
- Akaunti ya **owner/staff ya platform** haingii hapa; ni church members na church staff tu

OTP ya web (SMS) **haitumiki** kwenye API. Baada ya password sahihi, token inatolewa moja kwa moja.

Login inazuiliwa: **max 5 majaribio kwa dakika** kwa IP moja.

---

## Muhtasari wa endpoints

| Method | Endpoint | Auth | Maelezo |
|---|---|---|---|
| `POST` | `/api/login` | Hapana | Ingia, pata token |
| `POST` | `/api/logout` | Token | Futa token ya sasa |
| `GET` | `/api/user` | Token | User aliyelogin |
| `GET` | `/api/members` | Token + `members.view` | Orodha ya washirika (staff) |
| `GET` | `/api/members/{id}` | Token + policy | Mshirika mmoja |
| `GET` | `/api/member/dashboard` | Token + member portal | Dashboard ya mshirika |
| `GET` | `/api/member/profile` | Token + member portal | Profile yangu |
| `PUT` / `POST` | `/api/member/profile` | Token + member portal | Sasisha simu / picha |
| `PUT` | `/api/member/profile/password` | Token + member portal | Badilisha password |
| `GET` | `/api/member/announcements` | Token + member portal | Matangazo |
| `GET` | `/api/member/announcements/{id}` | Token + member portal | Tangazo moja |
| `GET` | `/api/member/leaders` | Token + member portal | Viongozi |
| `GET` | `/api/member/services` | Token + member portal | Huduma zilizotengenezwa |
| `GET` | `/api/member/services/{id}` | Token + member portal | Maelezo ya huduma |
| `GET` | `/api/member/requests` | Token + member portal | Maombi yangu |
| `GET` | `/api/member/requests/meta` | Token + member portal | Types, viongozi, watoto |
| `POST` | `/api/member/requests` | Token + member portal | Tuma ombi |
| `GET` | `/api/member/requests/{id}` | Token + member portal | Ombi moja |
| `GET` | `/api/member/requests/{id}/certificate` | Token + member portal | PDF ya cheti |
| `GET` | `/api/pastor/dashboard` | Token + pastor | Dashibodi ya mchungaji |
| `GET` | `/api/pastor/members` | Token + pastor | Washirika |
| `GET` | `/api/pastor/members/{id}` | Token + pastor | Mshirika mmoja |
| `GET` | `/api/pastor/children` | Token + pastor | Watoto |
| `GET` | `/api/pastor/leaders` | Token + pastor | Viongozi |
| `GET` | `/api/pastor/leaders/{id}` | Token + pastor | Kiongozi mmoja |
| `GET` | `/api/pastor/departments` | Token + pastor | Idara |
| `GET` | `/api/pastor/departments/{id}` | Token + pastor | Idara moja na washirika wake |
| `GET` | `/api/pastor/announcements` | Token + pastor | Matangazo |
| `GET` | `/api/pastor/announcements/{id}` | Token + pastor | Tangazo moja |
| `GET` | `/api/pastor/services` | Token + pastor | Huduma |
| `GET` | `/api/pastor/services/{id}` | Token + pastor | Huduma moja |
| `GET` | `/api/pastor/events` | Token + pastor | Matukio maalum |
| `GET` | `/api/pastor/events/{id}` | Token + pastor | Tukio moja |
| `GET` | `/api/pastor/attendance` | Token + pastor | Mahudhurio |
| `GET` | `/api/pastor/bereavements` | Token + pastor | Misiba |
| `GET` | `/api/pastor/bereavements/{id}` | Token + pastor | Msiba mmoja |
| `GET` | `/api/pastor/member-requests` | Token + pastor | Maombi ya washirika |
| `GET` | `/api/pastor/member-requests/{id}` | Token + pastor | Ombi moja |
| `GET` | `/api/pastor/registrations` | Token + pastor | Maombi ya usajili |
| `GET` | `/api/pastor/registrations/{id}` | Token + pastor | Usajili mmoja |
| `GET` | `/api/pastor/branches` | Token + pastor | Matawi (kama yamewashwa) |
| `GET` | `/api/pastor/finance` | Token + pastor | Muhtasari wa fedha |
| `GET` | `/api/pastor/finance/approvals` | Token + pastor mkuu | Rekodi zinazosubiri idhini |

---

## 1. Login

`POST /api/login`

### Body

Tuma `email` **au** `identifier` (si lazima zote).

```json
{
  "email": "admin@church.org",
  "password": "your-password",
  "device_name": "flutter"
}
```

| Field | Type | Lazima | Maelezo |
|---|---|---|---|
| `email` | string | Ndiyo* | Email au member number |
| `identifier` | string | Ndiyo* | Badala ya `email` |
| `password` | string | Ndiyo | Password ya akaunti |
| `device_name` | string | Hapana | Default: `flutter`. Token ya zamani yenye jina hili inafutwa |

\* Lazima moja kati ya `email` au `identifier`.

### Success — `200`

```json
{
  "success": true,
  "message": "Login successful",
  "token": "1|xxxxxxxx",
  "token_type": "Bearer",
  "user": {
    "id": 12,
    "uuid": "…",
    "name": "Jane Admin",
    "email": "admin@church.org",
    "phone": "+2557…",
    "user_type": "church_admin",
    "status": "active",
    "church_id": 3,
    "branch_id": null,
    "member_id": null,
    "church_role": "Administrator",
    "roles": ["administrator"],
    "last_login_at": "2026-09-17T10:00:00+00:00"
  }
}
```

Hifadhi `token` (secure storage). Usihifadhi password.

### Makosa

| Status | Message (mfano) |
|---|---|
| `401` | `Invalid credentials` |
| `403` | Akaunti si active, si church user, haina church, au church imesimamishwa |
| `422` | Validation (email/password haipo) |

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## 2. Logout

`POST /api/logout`

Inafuta token ya sasa tu. Token zingine (vifaa vingine) zinaendelea.

### Success — `200`

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 3. Current user

`GET /api/user`

### Success — `200`

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 12,
    "uuid": "…",
    "name": "Jane Admin",
    "email": "admin@church.org",
    "phone": "+2557…",
    "user_type": "church_admin",
    "status": "active",
    "church_id": 3,
    "branch_id": null,
    "member_id": null,
    "church_role": "Administrator",
    "roles": ["administrator"],
    "last_login_at": "2026-09-17T10:00:00+00:00"
  }
}
```

`user_type` unaweza kuwa: `church_admin`, `pastor`, `assistant_pastor`, `elder`, `secretary`, `treasurer`, `accountant`, `member`.

---

## 4. Orodha ya washirika

`GET /api/members`

Inatumia table **`members` iliyopo**. Inaonyesha washirika **active** wa kanisa la user pekee, pamoja na branch filter kama web.

### Ruhusa

- **Staff** wenye permission `members.view` wanaweza kuorodhesha.
- **Member wa kawaida** (bila `members.view`) anapata **`403`**. Anaweza kuona rekodi yake mwenyewe kwenye `GET /api/members/{id}`.

### Query parameters

| Param | Type | Default | Maelezo |
|---|---|---|---|
| `search` | string | — | Jina, member number, simu, au email |
| `name` | string | — | Jina tu |
| `member_id` | string | — | Member number |
| `member_number` | string | — | Sawa na `member_id` |
| `phone` | string | — | Simu |
| `email` | string | — | Email |
| `branch_id` | integer | — | Filter ya branch (admin/HQ) |
| `page` | integer | `1` | Ukurasa |
| `per_page` | integer | `20` | `1`–`100` |

Mfano:

```
GET /api/members?search=Jane&per_page=20&page=1
```

### Success — `200`

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 45,
      "uuid": "…",
      "church_id": 3,
      "branch_id": 1,
      "member_number": "WL-001",
      "envelope_number": "012",
      "full_name": "Jane Member",
      "email": "jane@example.com",
      "phone_number": "+255711111111",
      "gender": "female",
      "date_of_birth": "1990-01-15",
      "member_type": "independent",
      "membership_type": "permanent",
      "status": "active",
      "membership_date": "2020-03-01",
      "membership_expires_at": null,
      "profile_picture_url": "http://localhost/storage/…",
      "branch": {
        "id": 1,
        "name": "HQ"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  }
}
```

---

## 5. Mshirika mmoja

`GET /api/members/{id}`

`{id}` ni **ID ya database** (namba), si member number.

### Ruhusa

- Staff wenye `members.view` (na branch access)
- Member anaweza kuona **yeye mwenyewe** (na spouse ikiwa policy inaruhusu)

Mshirika wa kanisa lingine → `404` (si `403`), ili ID zisivujishwe.

### Success — `200`

Inajumuisha fields za orodha **pamoja na**:

- `education_level`, `profession`
- `is_baptized`, `baptism_date`, `baptism_place`
- `is_kipaimara`, `kipaimara_date`
- anwani: `region`, `district`, `ward`, `street`, `residence_*`, `address`, `city`
- ndoa: `marital_status`, `wedding_type`, `wedding_date`
- `spouse_full_name`, `spouse_phone_number`, `spouse_member_id`, `family_member_id`

**Hazitumwi:** password, NIDA, notes, archive internals.

---

## Status codes

| Code | Maana |
|---|---|
| `200` | Success |
| `201` | Created (haitumiki bado kwenye endpoints hizi) |
| `401` | Hakuna token, token si sahihi, au login imeshindikana |
| `403` | Akaunti/ruhusa haitoshi |
| `404` | Haipo |
| `422` | Validation |
| `500` | Server error |

### 401

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 422

```json
{
  "success": false,
  "message": "The identifier field is required.",
  "errors": {
    "identifier": ["Email or member ID is required."]
  }
}
```

---

## Flutter (mfano wa Dio)

```dart
final dio = Dio(BaseOptions(
  baseUrl: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
));

final login = await dio.post('/login', data: {
  'email': emailOrMemberNumber,
  'password': password,
  'device_name': 'flutter',
});

final token = login.data['token'] as String;
dio.options.headers['Authorization'] = 'Bearer $token';

final members = await dio.get('/members', queryParameters: {
  'search': query,
  'page': 1,
  'per_page': 20,
});
```

Baada ya `401` kwenye request yoyote, futa token na rudisha user kwenye login screen.

Dashboard ya mshirika:

```dart
final dashboard = await dio.get('/member/dashboard');
final profile = await dio.get('/member/profile');
final announcements = await dio.get('/member/announcements');
```

---

## Member dashboard (`/api/member/*`)

Hizi zinafanana na web portal `/my/*`. User lazima awe na `member_id` (rekodi ya mshirika). Data inatoka kwenye tables zilizopo.

Staff **bila** member record wanapata `403`.

### Dashboard

`GET /api/member/dashboard`

Inarudisha member, church, stats (membership, status, tithes/offerings za mwaka, open requests), recent requests, announcements, upcoming services, na leaders.

Tithes/offerings ni jumla ya mwaka huu **iliyoidhinishwa** ya mshirika huyo tu.

### Profile

`GET /api/member/profile` — member detail, church, departments, spouse, `family_dependants`.

Sasisha simu/picha. Flutter: **`POST` + multipart** kwa picha:

```
POST /api/member/profile
phone_number=+2557...
profile_picture=<file>
```

JSON: `PUT /api/member/profile` `{ "phone_number": "+2557..." }`

Password: `PUT /api/member/profile/password`

```json
{
  "current_password": "oldpass",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

### Announcements, leaders, services

```
GET /api/member/announcements
GET /api/member/announcements/{id}
GET /api/member/leaders
GET /api/member/departments
GET /api/member/events
GET /api/member/attendance
POST /api/member/attendance/scan
GET /api/member/giving
GET /api/member/services
GET /api/member/services/{id}
```

`GET /api/member/services` inaorodhesha huduma **zote zilizotengenezwa** (si upcoming tu). `?upcoming=1` inachuja zijazo. `per_page` default 15.

`GET /api/member/giving` inarudisha zaka, sadaka, na ahadi za mshirika pamoja na jumla. Departments ni idara hai za kanisa. Events ni matukio maalum. Attendance ni mahudhurio ya mshirika huyo.

`POST /api/member/attendance/scan` body: `{ "payload": "<check-in URL kutoka QR>" }`. QR ina URL ya check-in (signed). Simu inaweza kufungua URL moja kwa moja; app inaweza kutuma URL hiyo kwenye scan endpoint. Baada ya scan, mahudhurio yanarekodiwa. QR haipo kwa Shule ya Jumapili.

### Diary

```
GET /api/member/diary
POST /api/member/diary
PUT /api/member/diary/{id}
DELETE /api/member/diary/{id}
```

Kumbukumbu binafsi za mshirika. `POST`/`PUT` body: `{ "title": "optional", "body": "required" }`.

Matangazo yanachujwa kama web: active + targeted (all / department / specific).

### Requests

```
GET /api/member/requests?page=1&per_page=15
GET /api/member/requests/meta
POST /api/member/requests
GET /api/member/requests/{id}
GET /api/member/requests/{id}/certificate
```

`POST` body:

```json
{
  "type": "prayer_request",
  "subject": "Please pray for my family",
  "description": "…",
  "assigned_leader_id": 4
}
```

Baptism: `baptism_scope` = `self` | `children` | `both`. `child_dependant_ids` ni lazima kama scope ni `children` au `both`.

Cheti (PDF) kinapatikana baada ya ombi `approved` au `completed` kwa `travelling_certificate`, `recommendation_letter`, `baptism_certificate`.

Types: `travelling_certificate`, `recommendation_letter`, `baptism_certificate`, `baptism_request`, `general_issue`, `prayer_request`, `other`.

---

## Pastor API (`/api/pastor/*`)

Hizi ni za **mobile app ya mchungaji**. Kusoma na kuandika kunatumia **validation ileile ya fomu za web**.

### Nani anaingia

Login ni `POST /api/login` ileile. Akaunti lazima iwe:

| `user_type` | `church_role` | Anaingia? |
|---|---|---|
| `pastor` | Pastor | Ndiyo, pamoja na idhini za fedha |
| `assistant_pastor` | Assistant Pastor | Ndiyo, isipokuwa `/api/pastor/finance/approvals` |
| `member`, `secretary`, `treasurer`, `accountant`, `elder`, `church_admin` | — | Hapana. Wanapata `403` |

Baada ya login, angalia `user.user_type`. Ikiwa ni `pastor` au `assistant_pastor`, fungua skrini ya mchungaji.

```dart
final dashboard = await dio.get('/pastor/dashboard');
```

Header:

```http
Authorization: Bearer {token}
Accept: application/json
```

Bila token: `401` `{ "success": false, "message": "Unauthorized" }`.  
Si mchungaji: `403` `{ "success": false, "message": "This area is for pastors only." }`.

Orodha zote zinarudisha:

```json
{
  "success": true,
  "message": "Success",
  "data": [],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 0
  }
}
```

`page` default `1`. `per_page` default `20`, kikomo `100`. `{id}` ni ID ya database, si member number.

---

### Vitendo (kuandika)

Field za `POST` ni zile zile za fomu ya web. Orodha ya chaguo (membership type, tribe, preacher type, na washirika wa kuchagua) inatoka kwenye:

```
GET /api/pastor/members/form
GET /api/pastor/services/form
```

Picha ya mshirika: `POST /api/pastor/members` kama **multipart**, field `profile_picture`.

| Method | Endpoint | Kitendo |
|---|---|---|
| `POST` | `/api/pastor/members` | Sajili mshirika (field zote za fomu ya web, pamoja na mke/mume, familia, na `dependants`) |
| `POST` | `/api/pastor/children` | Ongeza mtoto |
| `POST` | `/api/pastor/leaders` | Teua kiongozi |
| `POST` | `/api/pastor/departments` | Tengeneza idara |
| `POST` | `/api/pastor/announcements` | Tangazo. `target_type=department` linahitaji `department_id`. `specific` linahitaji `member_ids` |
| `POST` | `/api/pastor/services` | Huduma. Mhubiri: `preacher_type` = `pastor` / `leader` / `member` / `guest`, na `preacher_member_id` au `preacher_guest_name` + `preacher_guest_phone` |
| `POST` | `/api/pastor/events` | Tukio maalum |
| `POST` | `/api/pastor/attendance` | Hifadhi mahudhurio (`source_type`, `source_id`, `member_ids`, `dependant_ids`, `guests_count`) |
| `POST` | `/api/pastor/bereavements` | Msiba |
| `POST` | `/api/pastor/member-requests/{id}/respond` | Jibu ombi la mshirika |
| `POST` | `/api/pastor/registrations/{id}/approve` | Kubali usajili |
| `POST` | `/api/pastor/registrations/{id}/reject` | Kataa usajili |
| `POST` | `/api/pastor/finance/tithes` | Rekodi zaka (inasubiri idhini) |
| `POST` | `/api/pastor/finance/offerings` | Rekodi sadaka |
| `POST` | `/api/pastor/finance/expenses` | Rekodi matumizi |
| `POST` | `/api/pastor/finance/pledges` | Rekodi ahadi |
| `POST` | `/api/pastor/finance/budgets` | Rekodi bajeti |
| `POST` | `/api/pastor/finance/approvals/approve` | Idhinisha. Body: `{ "type": "tithe", "id": 8, "approval_notes": "optional" }` |
| `POST` | `/api/pastor/finance/approvals/reject` | Kataa. Body: `{ "type": "tithe", "id": 8, "rejection_reason": "required" }` |

`type` ya idhini: `tithe`, `offering`, `budget`, `expense`, `pledge_payment`. Assistant pastor hawezi approve/reject.

Mfano wa mshirika (sehemu ya lazima; field zingine ni optional kama kwenye web):

```json
{
  "membership_type": "permanent",
  "member_type": "father",
  "full_name": "Neema Pastor",
  "gender": "male",
  "date_of_birth": "1992-04-04",
  "phone_number": "+255744444444",
  "envelope_number": "101",
  "marital_status": "single",
  "profession": "Teacher",
  "region": "Dar es Salaam",
  "is_baptized": true,
  "baptism_place": "Mwanza"
}
```

Ikiwa `marital_status` ni `married`, tuma pia `wedding_type`, `spouse_church_member` (`yes`/`no`), na ama `spouse_member_id` (select) au jina, jinsia, na tarehe ya kuzaliwa ya mwenzi (manual). `member_type=independent` linahitaji `family_parent_type`. Bahasha (`envelope_number`, tarakimu 3) inahitajika kwa umri uleule kama kwenye web.

Field iliyokosekana inarudisha `422` na `errors`.

---

### Dashboard

`GET /api/pastor/dashboard`

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "role": {
      "label": "Pastor",
      "user_type": "pastor",
      "is_pastor": true,
      "is_assistant_pastor": false
    },
    "church": {
      "id": 3,
      "name": "Waumini Church",
      "email": "contact@church.org",
      "phone": "+255700000000",
      "currency": "TZS",
      "pastor_name": "Pastor James"
    },
    "active_branch": null,
    "stats": {
      "total_members": 120,
      "active_members": 110,
      "new_members_month": 4,
      "children": 30,
      "departments": 6,
      "leaders": 12,
      "monthly_attendance": 80,
      "upcoming_events_count": 2,
      "upcoming_services_count": 3,
      "monthly_income": 500000,
      "monthly_expenses": 120000,
      "net_income": 380000,
      "pending_approvals": 1,
      "pending_approvals_amount": 15000
    },
    "ministry": {
      "new_members_month": 4,
      "children": 30,
      "departments": 6,
      "leaders": 12,
      "upcoming_services": 3,
      "upcoming_events": 2
    },
    "finance": {
      "period": { "year": 2026, "month": 10, "label": "October 2026", "input": "2026-10" },
      "summary": {
        "total_income": 500000,
        "total_expenses": 120000,
        "net_balance": 380000,
        "pending_approvals_count": 1,
        "pending_approvals_amount": 15000
      },
      "income_breakdown": [],
      "recent_transactions": []
    },
    "pending_approvals": {
      "counts": { "tithe": 1, "offering": 0, "budget": 0, "expense": 0, "pledge_payment": 0 },
      "amounts": { "tithe": 15000, "offering": 0, "budget": 0, "expense": 0, "pledge_payment": 0 },
      "total_pending": 1,
      "total_pending_amount": 15000
    },
    "announcements": [],
    "upcoming_events": [],
    "upcoming_services": []
  }
}
```

`finance` na namba za mapato zipo kwa mwenye ruhusa `finance.view`. `pending_approvals` iko kwa mchungaji mkuu tu (`finance.approve`). Kwa assistant pastor field hiyo ni `null`.

---

### Washirika

```
GET /api/pastor/members
GET /api/pastor/members/{id}
GET /api/pastor/children
```

| Param | Inatumika | Maelezo |
|---|---|---|
| `search` | members, children | Jina, member number, simu, au email. Kwa watoto: jina au mlezi |
| `name` | members | Jina tu |
| `member_id` / `member_number` | members | Namba ya mshirika |
| `phone` | members | Simu |
| `email` | members | Email |
| `status` | members | Bila param, washirika **active** tu. Tuma `status` kuona hali nyingine |
| `branch_id` | members | Tawi, kama user anaweza kuona matawi yote |
| `page`, `per_page` | zote | Ukurasa |

`GET /api/pastor/members/{id}` inarudisha maelezo ya mshirika, na `departments`, `dependants`, `spouse_member` zikiwepo.

Watoto: `full_name`, `gender`, `date_of_birth`, `relationship`, `is_baptized`, na `member` (mzazi).

---

### Viongozi na idara

```
GET /api/pastor/leaders
GET /api/pastor/leaders/{id}
GET /api/pastor/departments
GET /api/pastor/departments/{id}
```

Viongozi ni **active** tu. `include_inactive=1` inaongeza waliostaafu. `search` inatafuta jina la mshirika, member number, simu, au `position_title`.

Kiongozi:

```json
{
  "id": 4,
  "position": "pastor",
  "position_label": "Pastor",
  "member": {
    "id": 45,
    "full_name": "Jane Member",
    "member_number": "WL-001",
    "phone_number": "+255711111111",
    "profile_picture_url": null
  },
  "appointment_date": "2024-01-01",
  "end_date": null
}
```

Idara: `name`, `description`, `status`, `members_count`, `head`, `branch`. Maelezo (`/{id}`) yanaongeza `members` (`id`, `full_name`, `member_number`, `phone_number`, `role`). `status` kwenye query: `active` au `inactive`.

---

### Matangazo, huduma, matukio

```
GET /api/pastor/announcements
GET /api/pastor/announcements/{id}
GET /api/pastor/services
GET /api/pastor/services/{id}
GET /api/pastor/events
GET /api/pastor/events/{id}
```

| Param | Endpoint | Maelezo |
|---|---|---|
| `search` | zote | Kichwa, maudhui, ukumbi, mhubiri, au msemaji |
| `include_inactive=1` | announcements | Matangazo yasiyo active |
| `upcoming=1` | services, events | Tarehe ya leo na zijazo tu |
| `status` | events | `scheduled`, `ongoing`, `completed`, `cancelled` |
| `branch_id` | services, events | Tawi |

Tangazo: `id`, `title`, `content`, `excerpt`, `type`, `type_label`, `is_pinned`, `start_date`, `end_date`, `department`, `created_at`.

Huduma: `title`, `service_type`, `service_date`, `start_time`, `end_time`, `theme`, `preacher`, `venue`, `status`, `branch`. Maelezo yanaongeza `service_type_label`, `status_label`, `coordinator`, `notes`.

Tukio: `title`, `category`, `category_label`, `event_date`, `start_time`, `end_time`, `speaker`, `venue`, `budget_amount`, `expected_attendance`, `guests_count`, `status`, `description`, `notes`, `branch`.

---

### Mahudhurio

`GET /api/pastor/attendance`

Default ni **mwezi huu** hadi leo. Tuma `from` na `to` (`YYYY-MM-DD`) kubadilisha kipindi.

| Param | Maelezo |
|---|---|
| `from`, `to` | Tarehe |
| `source_type` | `church_service` au `special_event` |
| `source_id` | ID ya huduma au tukio |
| `search` | Jina au member number |
| `branch_id` | Tawi |

Kila rekodi: `id`, `source_type`, `source_id`, `attended_at`, `notes`, `member`, `dependant`, `branch`.

---

### Misiba

```
GET /api/pastor/bereavements
GET /api/pastor/bereavements/{id}
```

`search` inatafuta `deceased_name` au `family_details`. `status`: `open` au `closed`.

Fields: `deceased_name`, `family_details`, `incident_date`, `contribution_start_date`, `contribution_end_date`, `status`, `status_label`, `notes`, `closed_at`, `affected_member`.

---

### Maombi ya washirika

```
GET /api/pastor/member-requests
GET /api/pastor/member-requests/{id}
```

| Param | Maelezo |
|---|---|
| `status` | `pending`, `in_review`, `approved`, `rejected`, `completed` |
| `type` | `travelling_certificate`, `recommendation_letter`, `baptism_certificate`, `baptism_request`, `general_issue`, `prayer_request`, `other` |
| `search` | Reference, subject, au description |
| `branch_id` | Tawi |

```json
{
  "id": 9,
  "reference_number": "REQ-001",
  "type": "prayer_request",
  "type_label": "Prayer request",
  "subject": "Prayer",
  "description": "…",
  "status": "pending",
  "status_label": "Pending",
  "member": {
    "id": 45,
    "full_name": "Jane Member",
    "member_number": "WL-001",
    "phone_number": "+255711111111"
  },
  "assigned_leader": null,
  "response": null,
  "responded_at": null,
  "has_certificate": false,
  "request_meta": null,
  "created_at": "2026-10-04T12:00:00+00:00"
}
```

---

### Usajili wa washirika

```
GET /api/pastor/registrations
GET /api/pastor/registrations/{id}
```

`status`: `pending`, `approved`, `rejected`. `search`: application number, jina, au simu.

Fields: `application_number`, `full_name`, `phone_number`, `status`, `assigned_envelope_number`, `profile_picture_url`, `registration_data`, `dependants_data`, `member_id`, `rejection_reason`, `reviewed_at`, `branch`, `created_at`.

---

### Matawi

`GET /api/pastor/branches`

Inapatikana tu kanisa lililowasha matawi na mchungaji mwenye `branches.view`. Vinginevyo `403`.

`include_inactive=1` inaonyesha matawi yasiyo active. `search`: jina, code, jina la mchungaji, au jiji.

Fields: `id`, `name`, `code`, `is_headquarters`, `is_active`, `pastor_name`, `phone`, `email`, `address`, `city`.

---

### Fedha

`GET /api/pastor/finance`

Muhtasari wa mwezi huu. Optional: `year` (`2000`–`2100`) na `month` (`1`–`12`).

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "currency": "TZS",
    "period": { "year": 2026, "month": 10, "label": "October 2026", "input": "2026-10" },
    "summary": {
      "total_income": 500000,
      "total_expenses": 120000,
      "expenses_year": 900000,
      "net_balance": 380000,
      "pending_approvals_count": 1,
      "pending_approvals_amount": 15000,
      "active_pledges": 3
    },
    "income_breakdown": [
      { "key": "tithes", "label": "Tithes", "amount": 300000, "percent": 60 }
    ],
    "income_trend": [],
    "recent_transactions": [
      {
        "type": "tithe",
        "label": "Tithe",
        "member": "Jane Member",
        "description": "Member tithe",
        "amount": 15000,
        "date": "2026-10-01"
      }
    ]
  }
}
```

`recent_transactions.type`: `tithe`, `offering`, `pledge`, `bereavement`.

### Idhini za fedha

`GET /api/pastor/finance/approvals`

**Mchungaji mkuu tu.** Assistant pastor anapata `403` `{ "success": false, "message": "Forbidden" }`.

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "currency": "TZS",
    "counts": { "tithe": 1, "offering": 0, "budget": 0, "expense": 0, "pledge_payment": 0 },
    "amounts": { "tithe": 15000, "offering": 0, "budget": 0, "expense": 0, "pledge_payment": 0 },
    "total_pending": 1,
    "total_pending_amount": 15000,
    "pending": {
      "tithe": [
        {
          "type": "tithe",
          "id": 8,
          "amount": 15000,
          "date": "2026-10-04",
          "member": "Jane Member",
          "description": "Tithe",
          "status": "pending",
          "reference_number": null,
          "notes": null,
          "created_at": "2026-10-04T12:00:00+00:00"
        }
      ],
      "offering": [],
      "budget": [],
      "expense": [],
      "pledge_payment": []
    }
  }
}
```

Aina: `tithe`, `offering`, `budget`, `expense`, `pledge_payment`. Kwa budget, `amount` ni `total_budget`.

---

## CURL

```bash
curl -X POST http://127.0.0.1:8000/api/login ^
  -H "Accept: application/json" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@church.org\",\"password\":\"secret\"}"
```

```bash
curl http://127.0.0.1:8000/api/members?search=Jane ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN"
```

Kabla ya kujaribu, endesha kwenye Laravel:

```bash
php artisan migrate
```

Hii inaunda table `personal_access_tokens` tu (Sanctum). Haibadili data ya members.

---

## Notes kwa Flutter team

1. Token ni siri — usiweke kwenye logs za production.
2. Members API inarudisha washirika wa **kanisa la user aliyelogin** tu.
3. CORS imewekwa kwa `api/*`. App ya native haihitaji CORS; inahitajika hasa kama utatumia web/PWA.
4. Member dashboard (`/api/member/*`) inahitaji user aliyeunganishwa na rekodi ya `members`. Staff bila member record wanapata `403`.
5. Pastor API (`/api/pastor/*`) ni ya `pastor` na `assistant_pastor` tu. Login ni endpoint ileile; baada ya token, tumia `/pastor/dashboard`. Assistant pastor hawezi kuita `/pastor/finance/approvals`.
