# Task: Make collecting data from /doh-dashboard to supplier_id

## Steps

- [x] Step 1: Plan approved
- [x] Step 2: Update `routes/web.php` — Changed `doh-dashboard` and `gso-dashboard` routes to accept `{supplier}` parameter with route-model binding
- [x] Step 3: Update `SupplierController.php` — Updated `dohDashboard()` and `gsoDashboard()` to accept `Supplier $supplier`, validate type (DOH/GSO), scope all queries by `supplier_id`
- [x] Step 4: Update `layouts/app.blade.php` — Removed DOH/GSO Dashboard sidebar links
- [x] Step 5: Update `suppliers/show.blade.php` — Added "View Full Dashboard" button linking to supplier-specific dashboard + computed `$dashboardRoute`
- [x] Step 6: Update `doh/dashboard.blade.php` — Updated heading to show supplier company name
- [x] Step 7: Update `gso/dashboard.blade.php` — Updated heading to show supplier company name

