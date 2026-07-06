
## About This Project

This is a Laravel URL short formatter project. The current foundation includes
session-based authentication for registration, login, logout, and a protected
dashboard at `/dashboard`.

Authenticated users can create short links from the dashboard, review their own
generated short URLs, see redirect counts, open or copy short URLs, and delete
links they own. Deletion uses soft deletes, removes the link from normal
dashboard listings, and makes the public slug return a 404.
