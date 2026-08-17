# Admin user and role management

The access-control API uses the existing Spatie roles and permissions tables and the `sanctum` guard. Every admin user is assigned one role by the management API.

## Endpoints

| Method | Endpoint | Required permission | Purpose |
| --- | --- | --- | --- |
| GET | `/api/v1/permissions` | `ViewAny_Role` | List assignable permissions |
| GET | `/api/v1/roles` | `ViewAny_Role` | Search and paginate roles |
| POST | `/api/v1/roles` | `Create_Role` | Create a role with optional permissions |
| GET | `/api/v1/roles/{role}` | `View_Role` | Get a role and its permissions |
| PATCH | `/api/v1/roles/{role}` | `Update_Role` | Rename a role and optionally replace permissions |
| PUT | `/api/v1/roles/{role}/permissions` | `Update_Role` | Replace all permissions assigned to a role |
| DELETE | `/api/v1/roles/{role}` | `Delete_Role` | Delete an unassigned role |
| GET | `/api/v1/users` | `ViewAny_User` | Search users and filter by role |
| POST | `/api/v1/users` | `Create_User` | Create a user and assign one role |
| GET | `/api/v1/users/{user}` | `View_User` | Get a user and role details |
| PATCH | `/api/v1/users/{user}` | `Update_User` | Update profile, password, or assigned role |
| DELETE | `/api/v1/users/{user}` | `Delete_User` | Revoke tokens and delete a user |

Role names are normalized to lowercase snake case. Permission assignments use the exact permission names returned by the permission catalog. User creation requires `password_confirmation`; password is optional during updates.

The `super_admin` role is system-managed and cannot be renamed, edited, or deleted. Only a super administrator can assign that role or modify another super administrator. Users cannot change their own role or delete themselves, and the final super-administrator account cannot be deleted or demoted. Assigned roles must be removed from users before deletion.
