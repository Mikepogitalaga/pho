const fs = require("fs");
const file = "resources/views/receivings/index.blade.php";
let c = fs.readFileSync(file, "utf8");

// Fix 1: close section-header div
c = c.replace(
  "</div>\n\n    <section class=\"card\"",
  "</div>\n    </div>\n\n    <section class=\"card\""
);

// Fix 2: close empty-state div
c = c.replace(
  "<div class=\"empty-state\">\n                                    <strong>No receiving records found.</strong>\n                                    <div style=\"margin-top: 0.35rem;\">Create a new receiving slip to start tracking incoming stock.</div>\n                            </td>",
  "<div class=\"empty-state\">\n                                    <strong>No receiving records found.</strong>\n                                    <div style=\"margin-top: 0.35rem;\">Create a new receiving slip to start tracking incoming stock.</div>\n                                </div>\n                            </td>"
);

// Fix 3: close filters section-header
c = c.replace(
  "            </div>\n\n        <form id=\"receivingsFilterForm\"",
  "            </div>\n        </div>\n\n        <form id=\"receivingsFilterForm\""
);

fs.writeFileSync(file, c);
console.log("Fixed successfully");
