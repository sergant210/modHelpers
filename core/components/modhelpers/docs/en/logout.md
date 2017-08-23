## logout
Force logout the current authenticated user.

```logout($redirect = false, $code = 401, $relogin = true)) :bool```

- $redirect (bool) - Redirect after logout.
- $code (int) - Response code for redirection - 401, 403, 404.
- $relogin (bool) - True - login as an admin if authenticated in the mgr context.