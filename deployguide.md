UPDATE wpli_options 
SET option_value = REPLACE(option_value, 'http://local-skylark-20-09-26.test', 'https://skylarkapparelltd.com')
WHERE option_name IN ('siteurl', 'home');

