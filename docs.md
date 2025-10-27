# Branch Manager - Docs

## Entities

### User

| Column      | Type         | Constraints   | Description                             |
|-------------|--------------|---------------|-----------------------------------------|
| id          | int(11)      | PK, AI        | Unique user ID                          |
| identifier  | varchar(256) |               | Login identifier (e.g., username/email) |
| secret_hash | text         |               | Hashed password or secret               |
| name        | varchar(128) |               | Display name                            |
| rights      | int(11)      | DEFAULT 0     | Access level or permission flags        |
| status      | int(11)      |               | Account status (e.g., active/suspended) |
| type        | int(11)      |               | User type or role category              |
| last_seen   | timestamp    | NULLABLE      | Last login or activity timestamp        |
| created_at  | timestamp    | DEFAULT NOW   | Account creation time                   |

---

### Branch

| Column      | Type         | Constraints   | Description                             |
|-------------|--------------|---------------|-----------------------------------------|
| id          | int(11)      | PK, AI        | Unique location ID                      |
| name        | varchar(256) |               | Location name                           |
| coordinates | varchar(256) |               | GPS or map coordinates                  |
| address     | varchar(256) |               | Physical address                        |
| employees   | int(11)      |               | Number of employees                     |
| description | text         |               | Extended notes or details               |

---

### BranchHasSpecialization

| Column                 | Type      | Description                             |
|------------------------|-----------|-----------------------------------------|
| branchId               | int(11)   | FK referencing a branch                 |
| branchSpecializationId | int(11)   | FK referencing a specialization         |

---

### BranchHasUser

| Column     | Type      | Description                             |
|------------|-----------|-----------------------------------------|
| branchId   | int(11)   | FK referencing a branch                 |
| userId     | int(11)   | FK referencing a user                   |
| userRights | int(11)   | Access level or permission flags        |

---

### BranchSpecialization

| Column      | Type         | Description                             |
|-------------|--------------|-----------------------------------------|
| id          | int(11)      | PK, AUTO_INCREMENT                      |
| name        | varchar(128) | Specialization name or label            |
| description | text         | Optional notes or details               |

---

### Log

| Column     | Type         | Description                             |
|------------|--------------|-----------------------------------------|
| id         | int(11)      | PK, AUTO_INCREMENT                      |
| timestamp  | timestamp    | Time of the logged event                |
| userId     | int(11)      | Nullable; triggering user ID            |
| clientIp   | varchar(128) | IP address of the client                |
| action     | varchar(128) | Action performed (e.g. login)           |
| targetType | varchar(128) | Type of object affected                 |
| targetId   | int(11)      | ID of the affected object               |
| status     | int(11)      | Status code (e.g. success, failure)     |
| message    | text         | Optional message or error detail        |

---

# Login and Authorization

## Login

Endpoint: POST /login
Send as form-data: 
```
identifier=LOGIN_NAME
secret=RAW_PASSWORD
storeLogin=0|1
```

After successful login client receives 2 tokens:
short term access token (access token) - lasts 15 minutes
long term access token (refresh token) - lasts 30 days

Response:
```
{"access_token":"ACCESS_TOKEN","refresh_token":"REFRESH_TOKEN","expires_in":ACCESS_TOKEN_TTL_IN_SECONDS}
```
 
## Authorization

For fast authorization always use “access token“. Send it as a header in every HTTP call.
```
Authorization: Bearer ACCESS_TOKEN
```

 
When this token expires then refresh the authorization with “refresh token“ calling POST /authrefresh endpoint.
Send the refresh token as a header. You will receive a new “access token“ and also a new “refresh token“. Keep them both.
```
X-Refresh-Token: REFRESH_TOKEN
```
Response:
```
{"access_token":"ACCESS_TOKEN","refresh_token":"REFRESH_TOKEN","expires_in":ACCESS_TOKEN_TTL_IN_SECONDS}
```

Old refresh token is revoked and cannot be used for another refreshing of access token.
You have to use a newly generated refresh token for another access token refresh.

After “refresh token“ expires then you have to make a new login.
