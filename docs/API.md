# API reference (Parts 1-2)

Base URL: `/api/v1`. All responses: `{ "success": bool, "message": str, "data": ..., "errors": ..., "meta": ... }`.
Auth: `Authorization: Bearer <token>` (token from register/login). Pagination: `?page=1&per_page=20` (max 100) -> `meta{current_page,per_page,total,last_page}`.
Errors: 401 unauthenticated, 403 forbidden, 404 not found, 409 conflict, 422 validation (`errors{field:[msgs]}`), 429 rate limited, 500.

## Auth (public, throttle 10/min per IP)
| Endpoint | Body | Notes |
|---|---|---|
| POST /auth/register | name, email, password (min 8, letters+numbers), role (`faculty`\|`company_main`\|`company_branch`), phone?, **company_name** (required for company_main), industry?, city?, city_code?, state?, country?, location? | 201 `{user, token, token_type}`. `admin` cannot self-register. company_main also creates the company. faculty gets an empty profile. |
| POST /auth/login | email, password | 200 `{user, token}`; 401 bad credentials; 403 inactive account |
| POST /auth/forgot-password | email | Always 200 (no account enumeration). Link: `{FRONTEND_URL}/reset-password?token=..&email=..` |
| POST /auth/reset-password | token, email, password | 200; 422 invalid/expired token. Revokes all tokens. |

## Auth (token required)
| Endpoint | Role | Notes |
|---|---|---|
| POST /auth/logout | any | revokes current token |
| GET /auth/me | any | user + role data (faculty profile / company / branch) |

## Faculty (role: faculty)
| Endpoint | Body | Notes |
|---|---|---|
| GET /faculty/profile | | profile + education, experience, skills |
| PUT /faculty/profile | name?, phone?, designation?, department?, college?, qualification?, experience_years?, bio?, location?, city?, education[]{degree,institution,field_of_study?,start_year?,end_year?}, experience[]{title,organization,location?,start_date?,end_date?,is_current?,description?}, skills[] | Arrays, when sent, replace the stored list. Photo/resume upload comes in Part 3. |

## Company
| Endpoint | Role | Notes |
|---|---|---|
| GET /companies/search?q= | any authenticated (throttle 60/min) | Matches name, company_code, city, location, state. Paginated. Each item has `location_display` e.g. `Hyderabad (HYD)` and `verification_status`. |
| GET /companies/{id} | any authenticated | public company details |
| POST /company | company_main | name, description?, website?, email?, phone?, industry?, address?, city?, city_code?, state?, country?, location?. 409 if one already exists |
| GET /company | company_main | own company + profile; 404 if none |
| PUT /company | company_main | same fields, all optional |

## Branch workflow
| Endpoint | Role | Notes |
|---|---|---|
| POST /branches/requests | company_branch | company_id, branch_name, city (required), city_code?, location?, state?, country?. 409 if user already has a branch or a pending request. Notifies company owner. |
| GET /branches/requests/mine | company_branch | own requests |
| POST /branches/requests/{id}/cancel | company_branch (own) | only pending -> `cancelled` |
| GET /branches/requests?status= | company_main | requests for own company (`pending`,`approved`,`rejected`,`cancelled`) |
| GET /branches/requests/{id} | owner company / requester / admin | |
| POST /branches/requests/{id}/approve | company_main (owner of that company only) | One DB transaction: lock request, create active branch with server-generated unique 5-digit code, mark approved, notify, audit-log. 409 if already processed. Returns the branch (with `branch_code`). |
| POST /branches/requests/{id}/reject | company_main (owner only) | reason? -> request `rejected`, branch user notified |
| GET /branches?status= | company_main | all own branches (`pending`,`active`,`suspended`) |
| GET /branches/{id} | owner company / the branch user / admin | |
| GET /branches/{id}/jobs | same as above | jobs posted by that branch (job endpoints in Part 3) |
| GET /branch/dashboard | company_branch | `unlocked:false` + latest request until approved; then company, branch (code, location), stats (jobs, applications, unread_notifications) |

## Rate limits
auth 10/min/IP, search 60/min, actions (branch request/approve/reject) 30/min, uploads 20/min (Part 3), general 120/min.
