
--- Advanced Web Programming ---
Group Practicum Jobsheet: Meeting 6 (Group 4 - SIB 3G)

---  Step 6 (Part 4): Testing POST /pos with curl  ---

I sent POST /pos requests with curl after fetching the CSRF token and session cookie from the /pos page, using the Accept: application/json header so Laravel responds with JSON. In the first case, I set qty to 0 with a valid product_id. The server responded with 422 Unprocessable Content and the message The items.0.qty field must be at least 1., which comes from the min:1 rule on items.*.qty. In the second case, I sent product_id=99999, which does not exist in the database. The server again responded with 422, this time with The selected items.0.product_id is invalid., from the exists:products,id rule.

The error keys use dot notation (items.0.qty and items.0.product_id), where the 0 indicates which cart row has the problem. Without the JSON header, Laravel responds with 302 and stores the error messages in the session, which is what a browser experiences. After both tests, Transaction::count() in tinker was still 2500, the same as before the requests, so requests rejected by validation do not save any data.

---  Step 6 (Part 5): Is a total field with the numeric rule enough to prevent manipulation?  ---

No, it is not enough. The numeric rule only checks the shape of the data, meaning whether the value is a number, not whether that number is correct. The client is fully under the user's control: with curl or browser DevTools, anyone can send total=1 for a Rp500,000 purchase, and that value still passes both numeric and min:0, so the server would store a fake total. Sending per-item prices from the form does not help either, since those can be forged too.

The solution is to trust no number from the client. The client only sends product_id and qty, and the server fetches prices from the database, computes price × qty for each row, and sums them into the total, as TransactionController::store() does. Because total is in $fillable, code like Transaction::create($request->all()) must also be avoided, since it would store the client-supplied total as is.