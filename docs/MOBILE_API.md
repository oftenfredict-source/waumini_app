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
