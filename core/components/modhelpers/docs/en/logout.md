##logout
Force logout the current authenticated user.

```
logout($redirect = false, $code = 401):bool
```

- $redirect (bool) - Redirect after logout.
- $code (int) - Response code for redirection - 401, 403, 404.