---

LIVE website:

ssh tristatecrcomnew@34.162.230.19 -p 39936
xGu79OcfkXkc6za

---

[x] search field with multiple words
[x] empty map when properties are not found
[x] listings - hide empty detail rows
[x] Summary: NOTES in the sheet (internal), external page it's fine.
[x] internal: key_tag and borough
[x] change Agents to Listing Agent
[x] redirect to log in instead of displaying message
[x] ranges for prices and spaces
[x] save results: alert for layer name

[o] research custom map zoom: zoom level from 1 to 24, fixed zoom produces akward results. for 1 property: zoom almost to the max
[o] custom map pins - waiting for the images
[o] internal map should be the same as the external map, including the info window
[o] layer name: automatically generated from the filters

[o] edit the title of the custom map - IN PLACE

---

> How do you appreciate the work done so far? It doesn't look good but it works — 
> The deadline for this is Aug 31? Confirmation — 
> I'm leaving from Sep 7? — 

- Aprox 1-2 days to launch it, test it, and document it.
- Up to 1 week for technical debt; to cleanup, improve, comment, and use the best practices. This is optional.

---

- Added the text search filtering to the master page
- Added an option page that shows the status of the last cron job run: https://tristatecrcom.kinsta.cloud/wp-admin/options-general.php?page=tristatecr-datasync
- Some data will not be displayed in the REST API unless it's accessed from the master page and the use is logged in. This is to prevent the data from being scraped by bots or other unauthorized users. IMPORTANT: I need a list of which specific fields should be HIDDEN from the REST API for external peopleusers. Example JSON was shared in Asana.
- Added clickable listings list to the shareble search page.
- Other minor bug fixes and improvements.

---

----

The initial weekly estimates were based on completing each step of the process before moving on to the next step. This was a very linear approach to the project. The actual progress was more iterative since I have to go back and forth between the steps to make sure that the data is being imported correctly and that the sync script is working as expected.

Debugging and inspecting the data (especially the data from Buildout) took a lot of time. I had to make sure that the data is being imported correctly and displayed correctly on the frontend.

Building the React frontend took more time than expected since I had to prototype the filtering functionality and link it the map functionality. I also had to write a lot of custom code to process all the listing data and make it available for the filtering.

Some issues took more time to launch and fix than estimated: from simple things like data coming in as formatted strings (prices and area) instead of integers, to more complex issues like the the address data from Google Sheets not matching 100% the address data from Buildout which made matching the listings difficult.

---

- 2 weeks late according to the initial estimations, freaking out. threatening to pull the funding.
- he has recordings of all the meetings.

---

Setting up server cron instead of wp-cron.php - https://easyengine.io/tutorials/wordpress/wp-cron-crontab/

wp cron event delete tristatecr_datasync_cron_hook

crontab -e
*/5 * * * * cd /www/tristatecrcom_218/public/; wp cron event run --due-now > /dev/null 2>&1

---

- Check if the sync cron is running
+ Clear Filters: not fully working
+ Saving layer: done partially, need to save the IDs to a particula 'tristate_listing_search' post type
+ Google Maps: need to add the markers for the results
+ Google Maps API key: AIzaSyCICZNrADzvZJ0vNnlx2yciUt5qF4goESY
+ Google Maps: dynamic center
+ Google Maps: marker colors

---

SSH KINSTA:

ssh tristatecrcom@35.236.219.140 -p 58429
IIzChQuo1KJjGwp

http://tristatecrcom.kinsta.cloud

---

> DONE: GET RID OF THE CSV AND JSON files before going LIVE
> tristate_datasync: PJAl u9tt 2lEm QH2D Cx0q hvzC

---

wp post delete $(wp post list --post_type='tsc_property' --format=ids) --force
wp post delete $(wp post list --post_type='tsc_search' --format=ids) --force
wp post delete $(wp post list --post_status=trash --format=ids)

wp datasync --skip=remote,csv

wp db import db-2023-08-01-bcd6ac6.sql - no listings
wp db import db-2023-08-01-dc6af04.sql - buildout listings

---

- Price is coming form the sheets
- Latitude and Longitude - everything is coming in from Buildout? Add them on the map - the filters update the map
- Each search result adds another layher to the map

1. Finishing the listings - putting in place the place data - name/price/neighbourgood/size/rent
2. Basic Google Map scatter
3. Basic filtering functionality & search - 
4. Expand the filtering functionality
5. Add each filter search as another layer on the Google Map
6. The ability to save the search along with the map so it can be shared

RANGE for Rent, Size
ZIP Code: dropdown with search filtering - SELECT2

Filtering start with all the data - grey out the ones that don't match - FIND A SOLUTION FOR THIS

Number of the search results - is displayed
ADD these results to the map

Content to WordPress - Ask Anton?

---
---

- Imported 825 properties: https://tristatecrcom.kinsta.cloud/wp-admin/edit.php?post_type=tsc_property
- Example of property imported from Buildout: https://tristatecrcom.kinsta.cloud/properties/for-sale-4400-sf-1642-undercliff-ave-bronx-multi-family
- Example of property imported from Google Sheets: https://tristatecrcom.kinsta.cloud/properties/71-e-mt-eden

- The next step would be to create a script that will sync the data regularly - every 5 minutes or so. This will be a cron job.

- Next: connect with Google Sheets and Buildout APIs to sync data on a regular basis - cron job
- Next: frontend - single property page and filtering

---

Week: Jul 10-Jul 17 the initial importer - IN PROGRESS
Week: Jul 17-Jul 24 the sync script & cron job
Week: Jul 24-Jul 31 the frontend - single property page and filtering
Week: Jul 31-Aug 7 the frontend - search results page and map page
Week: Aug 7-Aug 14 the frontend - search results page and map page

---

- A full Google Sheets and Buildout import currently takes about 120 seconds to complete - for a total of 819 properties.
- A subsequent run is much faster, taking about 4 seconds to update 10 records. This is good.
- Some properties from Google Sheets appear to be duplicated (4-8 items) or at least the addreses match. I'll investigate and we'll have to discuss how to avoid this so that the property data doesn't get mixed up.
- Currently I am NOT importing the media. I am thinking we can use the images attached to the properties without importing them to WP - this will make everything faster and easier to manage.
- There are many fields (~167) coming from Buildout so we will need to talk about which are going to be used and displayed on the site. Only ~30 fields are coming from the Sheet data.

---

https://docs.google.com/spreadsheets/d/{key}/gviz/tq?tqx=out:csv&sheet={sheet_name}
https://docs.google.com/spreadsheets/d/{key}/gviz/tq?tqx=out:json&sheet={sheet_name}

1rojsNCnghqxDc--prCkCEGjgVxSnCi3qcAxGlog_1lg
1896117761

https://docs.google.com/spreadsheets/d/1rojsNCnghqxDc--prCkCEGjgVxSnCi3qcAxGlog_1lg/gviz/tq?tqx=out:csv&sheet=1896117761
https://docs.google.com/spreadsheets/d/1rojsNCnghqxDc--prCkCEGjgVxSnCi3qcAxGlog_1lg/gviz/tq?tqx=out:json&sheet=1896117761

---

Example of property already on the site:

https://tristatecr.com/inventory-NY?propertyId=1641-fairystone-park-highway-stanleytown-sale

- aprox 1 day to launch it, test it, and document it.
- up to 1 week for technical debt; to cleanup, improve, comment, and use the best practices.

- search: update the number of listings after filtering
- link the listings from the map page

---

- a search field: searches through everything: all fields
- vented from sheets will be displayed for all listings (external)
- only properties for sale should have the price visible externally
- properties for lease will have the rent prices only internally
- for sale or for rent filters do not work properly

- remove all the fields containing landlord information from the listings

- DO NOT SHOW THE LISTINGS ON THE SEARCH PAGE
- Listings: restrict the page to internal people

Move this on the live website: 



---

Wednesday:

- [x] filter by text field is needed
- [x] vented field is needed
- [x] only properties for sale should have the price visible externally
- [x] infoWindow: which information to display - image + FOR_SALE + property_name/property_title + LINK to the property_page from sheets
- [x] infoWindow: add a link to the property page: _gsheet_link_to_more_info

---

TOMEK

Please let me know how you appreciate the progress so far and what is left to do. I need a clear list of tasks to focus on for the remaining time until August 31.

---

According to our initial discussion, the 6 weeks estimation for this project was very tight and I have mentioned that 6 to 8 weeks is more realistic.

The weekly timeline I have provided you with was an estimation, I see in Asana that you have considered it a strict schedule.

You said we are 3 weeks late on a 6 weeks project, however that's not actually the case judging from the progress. The estimates were based on a linear progress wich was not possible, the progress was more iterative as I've had to switch between different tasks and features to make sure we are on track.

---

- The initial project estimation was 6-8 weeks. I've started working with them on July 5. According to their Asana task I'm working on "website migration from Google Sites to wordpress" the deadline is set for August 31.

- Client asked me for a weekly timeline and I have estimations for the first 3 weeks considering a linear progress of the project.
- The progress was more iterative as I've had to switch from one task to another and back again.
- We've had 2 meeting every week so he was up to date with the project progress.
- Starting from 10-11 August he claimed we are 3 weeks behind schedule.
- I've tried to explain him that the project is not linear and that we are not behind schedule by pointing out where we are in the project and what is left to do.

- The Agent Dashboard was not part of the original scope. I always considered a different project since he told me in the beggining he wants to keep working with me if he's happy.
- On Asana I've seen he is talking about "financial implication of the project going over the expected timeline".