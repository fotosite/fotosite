git add .
git commit -m "sessiondb.session: user_type/cust_id/mand_id/syst_id korrekt befuellt (App::terminating), verwaiste Sessions durch regenerate(true) behoben, destroy() ID-Kuerzung korrigiert"
git tag session_usertype_fix_ok
git push
git push origin session_usertype_fix_ok