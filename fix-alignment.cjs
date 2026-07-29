const fs = require("fs");
let c = fs.readFileSync("resources/views/layouts/app.blade.php", "utf8");

// Fix 1: Remove extra closing divs and fix indentation for sidebar-brand
c = c.replace(/                    <\/div>\n                <\/div>\n            <\/div>\n                <button type="button" class="sidebar-collapse-btn"/g, '                <\/div>\n                <button type="button" class="sidebar-collapse-btn"');

// Fix 2: Fix app-topbar-start closing and indentation
c = c.replace(/                <\/div>\n            <\/div>\n\n                <div class="app-topbar-end">/g, '                <\/div>\n            <\/div>\n\n            <div class="app-topbar-end">');

// Fix 3: Fix topbar-profile-meta closing and indentation
c = c.replace(/                                <span class="topbar-profile-role">{{ auth\(\)->user\(\)->email \?\? \\'PHO Admin\\' }}<\/span>\n                <svg/g, '                                <span class="topbar-profile-role">{{ auth()->user()->email ?? \\'PHO Admin\\' }}<\/span>\n                    <\/span>\n                    <svg');

// Fix 4: Fix header closing and indentation
c = c.replace(/                        <\/div>\n                    <\/div>\n                <\/div>\n                <\/div>\n            <\/header>/g, '                        <\/div>\n                    <\/div>\n                <\/div>\n            <\/div>\n        <\/header>');

fs.writeFileSync("resources/views/layouts/app.blade.php", c);
console.log("Layout alignment fixed!");