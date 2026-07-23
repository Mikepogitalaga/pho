# Dashboard Redesign TODO

## Step 1: Update DashboardController.php
- [x] Add current stock sum query
- [x] Add total received all-time query  
- [x] Add total released all-time query
- [x] Add expiring items count query
- [x] Add inventory value query (qty_on_hand * unit_cost)
- [x] Add supply movement trend data (12 months)
- [x] Add inventory by category data (grouped)
- [x] Add top 10 most released items data
- [x] Add monthly receiving by supplier data
- [x] Add releases by facility data
- [x] Add stock status distribution data

## Step 2: Update dashboard.blade.php
- [x] Redesign top section with 8 KPI cards
- [x] Add Supply Movement Trend line chart
- [x] Add Inventory by Category pie chart
- [x] Add Top 10 Most Released Items horizontal bar
- [x] Add Monthly Receiving by Supplier bar chart
- [x] Add Releases by Facility horizontal bar
- [x] Add Stock Status pie chart
- [x] Add bottom tables section (Recent Receivings, Releases, Low Stock, Expiring)

## Step 3: CSS Updates
- [x] Add new KPI card color variants (teal, amber, red, primary)
- [x] Add dashboard-chart-grid (2 columns)
- [x] Add dashboard-tables-grid (2 columns)
- [x] Add chart-container--tall variant
- [x] Update responsive breakpoints for new grids

## Step 3: Test
- [x] PHP syntax check passed
- [ ] Verify dashboard renders without errors in browser
- [ ] Verify all charts display correctly

