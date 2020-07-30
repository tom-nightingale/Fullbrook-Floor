=== PropertyHive Property Import ===
Contributors: PropertyHive,BIOSTALL
Tags: property import, property hive, propertyhive, blm import property, expertagent, vebra, dezrez, jupix, real estate, software, estate agents, estate agent, property management
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.4.2
Stable tag: trunk
Version: 1.1.82
Homepage: http://wp-property-hive.com/addons/property-import/

This add on for Property Hive imports properties from another source into WordPress

== Description ==

This add on for Property Hive imports properties from another source into WordPress

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Property Import plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the import tool by navigating to 'Property Hive > Import Properties' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.82 =
* Catered for MRI XML format having media sent in different format
* Import available date from AgentOS
* Set timeout on requests to Arthur to try and avoid requests timing out and hitting the default 5 second limit

= 1.1.81 =
* Added new option to Arthur format to specify only top-level property should be imported with no units
* Import virtualtour node send in Gnomen XML format

= 1.1.80 =
* Add filter to each format allowing customisation of address fields to check when auto-mapping property to location
* Geocoding requests to be over HTTPS in AgentOS format

= 1.1.79 =
* Updates to Gnomen format including support for commercial properties, brochures, EPCs and Virtual Tours
* Run EPC URLs sent by MRI through html_entity_decode() function

= 1.1.78 =
* Import virtual tours in Dezrez JSON format that are hosted on Rezi servers instead of being on YouTube etc
* Added more debugging to AgentOS logs when issue arises obtaining or parsing responses

= 1.1.77 =
* Change field being used as unique identifier in REX format
* Assign properties to negotiator based on name in REX format
* Extend limit on number of properties received in REX format
* Don't include withdrawn properties in REX format

= 1.1.76 =
* Added parking and outside space as mapped fields in SME Professional format
* Use 'suppress_filters' when removing properties from Arthur format to ensure units are included

= 1.1.75 =
* Import availability in AgentOS format
* Ensure property types can be mapped in Kyero format
* Set featured in Agency Pilot API format
* Correctly categorise brochures and EPCs in Agency Pilot API format
* Import all features from SME format

= 1.1.74 =
* Correct size from/to and units in agentsinsight* format
* Import URL set in particulars_url field as brochure when present in agentsinsight* format
* Reduce number of requests made per minute in AgentOS format to prevent hitting strict throttling limits. In future might need to look at adding a 'Only import updated properties' option (assuming dates are sent)
* Don't keep doing geocoding requests if one is denied in agentOS format
* Added filter to AgentOS format (propertyhive_agentos_json_properties_due_import) to filter properties pre-import

= 1.1.73 =
* Ignore properties in Acquaint XML format that have 'feedto' set to 'none'

= 1.1.72 =
* Cater for lat/lngs being zero as well as empty strings when deciding whether to do geocoding fallback to obtain co-ordinates

= 1.1.71 =
* Corrected address fields imported in Dezrez XML format ensuring house number is imported and putting town into the town field in Hive
* Corrected issue with thumbnail image being imported in MRI format
* Corrected issue with floorplans not importing in MRI format
* Declare support for WordPress version 5.4.2

= 1.1.70 =
* Corrected issue with new MRI format not making request
* Corrected wrong floor area unit being imported from Agency Pilot API

= 1.1.69 =
* Added support for MRI XML format
* Split out sales and lettings availabilities in Dezrez JSON format

= 1.1.68 =
* Added support for AgentOS/LetMC API format
* Tweaked address fields imported in 10ninety format

= 1.1.67 =
* Added code to do redirects from mailouts from third party software. For Jupix this will look for the format http://website-url.com?profileID={property-id-in-jupix} and for all other formats the following can be used: http://website-url.com?imported_id={property-id-in-software}
* Tweaks to Rex format regarding display address and summary description

= 1.1.66 =
* Added next and previous buttons to logs to allow quickly cycling through them
* Added ability to force import to run by adding &force=1 to manual execution. Reduces the need to wait 5 minutes when we know it's definitely fallen over or when debugging
* Added support for new epcgraph field in EstatesIT format
* Catered for ampersands in data when importing CSVs for fields where there's a list of possible values

= 1.1.65 =
* Added support for Decorus / Landlord Manager XML feed

= 1.1.64 =
* Updated Arthur format to only import properties that have available or under offer units
* Catered for properties from Arthur with no units

= 1.1.63 =
* Availabilities split out for formats that share statuses across departments
* Added ability to update properties in CSV using existing post ID
* Only update from CSV when field set as to not overwrite existing data
* Price qualifiers changed to not be case-sensitive in Vebra format. Testing with the ability to roll this out to all taxonomies across all formats in future

= 1.1.62 =
* Added support for Rex Software format
* Added new setting to Arthur format to specify if units should imported as their own properties
* Added floor area fields (albeit blank) to Dezrez formats for commercial properties so they at least appear in search results (due to floor area being the default sort order)
* Added new filters to Jupix image (propertyhive_jupix_image_url) and floorplan (propertyhive_jupix_floorplan_url) URLs. Useful if wanting to use a different size of image than large
* Declare support for WordPress version 5.4.1

= 1.1.61 =
* Added filters to Dezrez import formats to allow customisation of which property types are classed as commercial and should therefore put properties in the commercial department

= 1.1.60 =
* Added support for virtual tours to Loop format
* Catered for more than 100 properties in Aruthur Online results
* Don't process Acquaint data if feed couldn't be obtained/parsed
* Assigned properties to commercial department accordingly in Dezrez XML format when propertyTpye is commercial
* Set default URL for Loop format when setting up a new import
* Check more fields when auto-matching location in agentsinsight* format
* Declare support for WordPress version 5.4

= 1.1.59 =
* Catered for when no currency provided in Kyero XML format by using currency of country instead

= 1.1.58 =
* Renamed Thesaurus to Thesaurus / MRI
* Used lat/lng from Thesaurus geocode.file when available instead of making geocoding requests

= 1.1.57 =
* Added support for agentsinsight* XML
* First pass at automatically adding custom field mappings (mainly property type and availability) that don't exist yet to all formats to save having to go through logs to find which ones don't exist

= 1.1.56 =
* Specified timeout on Agency Pilot API requests to stop it timing out after the default 5 seconds
* Passed options and token through to Agency Pilot API pre-import hooks

= 1.1.55 =
* Corrected mapping of location in Agency Pilot REST API format
* Logged Vebra data in database to add some sort of debugging. At some point we'll a) roll this out to all formats and b) make it accessible on frontend

= 1.1.54 =
* Take into account child-parent locations relationships when validating and importing CSV files

= 1.1.53 =
* Updated list of property types in Dezrez format that determine whether a property should be assigned to commercial department

= 1.1.52 =
* Correction regarding date formatting in Vebra format when working out date to get changed properties from
* Correction regarding checking if EPC's imported from BLM previously or not. Catered for scenario where MEDIA_IMAGE_60 doesn't exist and only MEDIA_DOCUMENT_50 passed

= 1.1.51 =
* Put properties in commercial department accordingly from Dezrez when property type contains 'Commercial'
* Updated how VECO format obtains data by using wp_remote_get() instead of cURL.

= 1.1.50 =
* Updated how Dezrez JSON format obtains data by using wp_remote_post() instead of cURL. Also don't put downloaded data into a file removing issues with permissions etc

= 1.1.49 =
* Added separate commercial property type mapping to Vebra format
* Added ability to assign properties to Agent/Branch during a CSV import if the Property Portal Add On is active
* Declare support for WordPress version 5.3.2

= 1.1.48 =
* Updated BLM format so Geocoding requests aren't continuously performed if previously failed due to REQUEST_DENIED being returned
* Added new 'propertyhive_expertagent_departments_to_import' filter to ExpertAgent format so departments imported can be overwritten
* Updated setting of featured properties in Veco format to use update_post_meta instead of add_post_meta
* Declare support for WordPress version 5.3.1

= 1.1.47 =
* Added option to only import updated properties in the Veco format based on the 'UpdatedDate' provided
* Updated the Veco format so images/media are re-imported if 'UpdatedDate' on the property differs from last time they were imported. The URL's don't change so have no other way to determine whether they should be re-imported or not
* Ensure office is at least set to the primary in Kyero format
* Set frontend submission user ID in CSV import if match found and add on is active

= 1.1.46 =
* Use displayAddress field from Loop if present, without trying to construct it ourselves
* Corrected issue with features in Domus format not importing
* Corrected link to docs

= 1.1.45 =
* Import negotiator from JET/ReapIT format if user with same name exists
* Added new filter to allow JET SOAP Client options to be modified
* Continue to import property even if no units in Arthur format
* Set availability to Sold if soldDetails node exists in REAXML format
* Declare support for WordPress version 5.3

= 1.1.44 =
* Added new options to draft or delete property when taken off of the market. Note this will result in 404 errors as the proeprty URL is no longer accessible
* Added ability to set negotiator when importing properties via CSV
* Set reference number to 'my_unique_id' field in SME Professional format

= 1.1.43 =
* Removed previous department mapping Gnomen as noticed the 'transaction' field so use that

= 1.1.42 =
* Made department/category a mappable field in Gnomen format as it can differ per client

= 1.1.41 =
* Ensure mapping for parking and outside space is saved if exists
* Corrected removal of properties in ReaXML format

= 1.1.40 =
* Corrected field for property type in Gnomen format
* Corrected field for available date in Utili format
* Added filter to ignore whether portal add on active when removing properties
* Removed properties from Acquaint format with status 'ERROR'
* Declare support for WordPress version 5.2.4

= 1.1.39 =
* Activate new Geocoding API key setting under 'Property Hive > Settings > General > Map'. Used for when the main API key entered has a referer restriction applied and separate key required just for Geocoding requests.
* Use new Geocoding API key in requests if present when trying to get lat/lng from address, else fallback to original
* Override default limit of 20 records in Arthur requests

= 1.1.38 =
* Added room dimensions to JET format
* Added more default property type mapping in Utili format

= 1.1.37 =
* Corrected issue with property type not importing in Utili format
* Trimmed additional space from Vebra details entered as this sometimes caused support queries when extra spaces had been copied and pasted
* Imported correct unit value for price, rent and floor area when importing commercial properties via CSV
* Switched to new Loop API
* Changed Loop API to use wp_remote_get() instead of bespoke cURL request
* Added 'Sale Agreed' status to default list of availabilities in Utili format
* Stored HTTPS version of media when storing media as URLs in Jupix format
* Added support for taxonomies that support multiple values during CSV validation such as commercial property type

= 1.1.36 =
* Updated Arthur format to import floorplans and EPCs
* Updated SuperControl format to use GET instead of POST when requesting properties
* Updated SuperControl format to use propertyname as Display Address instead of propertytown
* Updated SuperControl format to import booked dates, allowing date/availability filter on frontend
* Corrected refreshing of Arthur token wiping out existing imports
* Declare support for WordPress version 5.2.3

= 1.1.35 =
* Jet/ReapIT format to look for 'Bedrooms' field if 'TotalBedrooms' field doesn't exist
* Juvo image index to start at 0 instead of 1

= 1.1.34 =
* Added prelimenary support for SuperControl API

= 1.1.33 =
* Updated Arthur format to import deposit, rent frequency and descriptions
* Updated Arthur format to always give images an extension
* Use ID as reference number in Acquaint format
* Import fees as a room in Acquaint format
* Import brochures from Acquaint format
* Use wp_remote_get() when trying to get feeds in Kyero format instead of adding our own fallbacks

= 1.1.32 =
* Updated Vebra format to cater for firmid when assigning properties to offices. The mapping can now be entered as {firmid}-{branchid} if same branchid shared across multiple firms within the same XML

= 1.1.31 =
* Corrected issue with Acquaint format importing and replacing the existing media every time it ran
* Updated Dezrez JSON format to import EPCs if sent in the 'Documents' field instead of the 'EPC' field

= 1.1.30 =
* Added support for Arthur format ready for initial testing. First release which requires the new Rooms and Student Accommodation add on
* Added warning to property record if property was imported by an import that no longer exists to aid debugging

= 1.1.29 =
* Added new option to only import updated properties in Jupix format
* Added new option to only import updated properties in Thesaurus format
* Removed 'Featured' and 'PriceOnApplication' as availability mappings in Dezrez JSON format as these were conflicting with Sold STC and other statuses
* Updated Vebra format to import commercial property full descriptions into correct description fields instead of rooms

= 1.1.28 =
* Write to log if status sent that's not mapped in WebEDGE format
* Improved price qualifier mapping in Dezrez JSON format
* Use RoleID as reference number in Dezrez JSON import
* Use wp_remote_get() to obtain remote files in Domus format
* Don't import properties with sale_stage sold, let or sold_or_let in Realla

= 1.1.27 =
* Ensure currency is stored when importing properties across all formats
* Look for office name or ID when deciding which office to assign properties to in Dezrez JSON format. Previously it would look at just name

= 1.1.26 =
* Added support for Utili API
* Few amendments to Juvo XML format based on responses from their developers

= 1.1.25 =
* Added ability for new formats to be added by third parties through use of new filters and actions
* Declare support for WordPress version 5.2.2

= 1.1.24 =
* Import Address4 field if present as county in ReapIT/JET format
* Try to auto assign properties to location in ReapIT/JET format
* Add Address4 field to geocoding request in ReapIT/JET format
* Cater for floorplans being sent as documents in Realla format

= 1.1.23 =
* Added support for remote BLM whereby BLM's are retrieved via URL instead of being sent via FTP
* Corrected POA and rent frequency in Agency Pilot JSON format
* Corrected wrong field name being used in EstatesIT setup
* Corrected potential parse error in BLM format after recent geocoding amend
* Corrected log regarding number of virtual tours imported in 10ninety and BLM formats

= 1.1.22 =
* Added support for Juvo XML
* Don't perform Google Geocoding request if no API key present and write to log
* Corrected featured properties not being set in Domus format

= 1.1.21 =
* Corrected wrong URL being used for new ReSales Online format

= 1.1.20 =
* Added support for ReSales Online XML
* Added 'Categories' to list of data retrieved from Agency Pilot API
* Declare support for WordPress version 5.2.1

= 1.1.19 =
* Correct format of available date imported in WebEDGE format
* Commercial properties imported via Jupix can now get assigned multiple property types

= 1.1.18 =
* Import reception rooms in SME format
* Re-download images and other media in Acquaint format if 'updateddate' field has changed
* Catered for rent being sent as 0 in Jupix format for commercial properties
* Don't process BLM if missing #DATA# or #END# tags
* Added new filter 'propertyhive_expertagent_unique_identifier_field' to change field used as unique ID in Expert Agent format

= 1.1.17 =
* Added support for Estates IT XML
* Added warning on JET / ReapIT format if SOAP not enabled
* Catered for price being sent as 0 in Jupix format for commercial properties
* Added support for 'sale by' for commercial properties in Jupix format
* Improved way in which Jupix XML is obtained and output response if failed
* Don't import on hold, withdrawn or draft properties in WebEDGE format
* Cater for querystring or no link direct to PDF in brochure URL in BLM
* Import 'big' image from Loop
* Removed duplicate </table> from CSV mapping stage
* Don't import room dimensions from WebEDGE if 0' 0"
* Added price qualifier support for PropertyADD format
* Corrected placeholder on PropertyADD URL input
* Declare support for WordPress version 5.2

= 1.1.16 =
* Added support for Eurolink Veco API format
* Added support for Loop API format
* Import available date from JET / ReapIT
* Only run DezrezOne XML imports if both sales and lettings parsed correctly

= 1.1.15 =
* Added support for Kyero XML format
* Completed integration testing for WebEDGE / Propertynews.com format

= 1.1.14 =
* Added support for SME Professional XML
* Corrected issue with brochures imported using Agency Pilot JSON format not importing correctly when URL's contain a query string
* Call update_property_price_actual() after importing commercial properties in BLM and CSV formats so prices and currencies get set accordingly which are then later used for ordering
* Added preliminary support for Gnomen (pending testing)
* Added preliminary support for WebEDGE / Propertynews.com (pending testing)
* Declare support for WordPress version 5.1.1

= 1.1.13 =
* Added office and negotiator mapping to Agency Pilot REST API format. Office mapping can be controlled when setting up the import and entering the ID of the office from Agency Pilot. When mapping the negotiator Property Hive will look for a WP user with the same name, otherwise will default to the current user.

= 1.1.12 =
* Catered for new media storage settings across all formats allowing media to be saved as URL's instead of actually downloaded
* Fixed Domus feed falling over when a property is missing a description
* Empty ph_featured_properties transient after import has completed

= 1.1.11 =
* Clean up media no longer used from all formats to assist with disk space growing over time storing old, unused media
* Import district and county in Agency Pilot REST API format
* Correct features not being imported correctly in Agency Pilot REST API format

= 1.1.10 =
* UTF8 encode room names and room descriptions from Thesaurus format

= 1.1.9 =
* Fixed commercial properties coming through as residential in ExpertAgent format when department was set to 'Commercial Sales' or 'Commercial Lettings'
* Swap Agency Pilot REST API over to using the new OAuth2 authentication
* Cater for ampersands being present in ExpertAgent branch names
* Declare support for WordPress version 5.0.3

= 1.1.8 =
* Revised way in which checking if import already running is done
* Try and auto-assign properties to location in PropertyADD format
* Changed logic of Expert Agent room imports in event rooms no longer exist
* Corrected available date imported from Acquaint
* Added price qualifier mapping to Acquaint format
* Added new filter (propertyhive_{format}_properties_due_import) to filter properties pre import
* Log post ID of property being removed
* Remove properties correctly in Vebra format where action is 'deleted'
* Declare support for WordPress version 5.0.1

= 1.1.7 =
* Limited log entries to 255 characters to prevent them exceeding DB limit and not getting logged
* Added support for commercial properties in ExpertAgent format
* Corrected HTML tags in PropertyADD descriptions coming through encoded

= 1.1.6 =
* Added support for PropertyADD XML format

= 1.1.5 =
* Added support for new Agency Pilot REST API format

= 1.1.4 =
* Corrected issue with Geocoding requests failing due to &amp; in URL instead of &
* Corrected issue with Geocoding request failures being logged due to length of error message
* Added setting link to main plugins page
* After importing media in Jupix format remove files that are not referenced

= 1.1.3 =
* Change geocoding requests so they're made over HTTPS to prevent failure

= 1.1.2 =
* Use uploaded date from Vebra as post date
* Added commercial tenure mapping to Reapit / JET format

= 1.1.1 =
* Improvements to Reapit / JET format to reduce chance of it timing out and removing all properties
* Added support for featured properties in Reapit / JET format
* Corrected default status for commercial properties in Reapit / JET format
* Corrected issue with mappings being duplicated in dropdown during mapping step of setting up new import

= 1.1.0 =
* Corrected issue with new 'Only import updated properties' option in Reapit / JET format
* Added link to logs to download processed BLM files to assist with support

= 1.0.99 =
* Added new option to Reapit / JET format to only import updated properties
* Added support for commercial properties to Reapit / JET format
* Added support for multiple residential property types in Reapit / JET format
* Catch invalid SOAP calls in Reapit / JET feed which would cause fatal error
* Fixed availability mapping in Agency Pilot format
* Added 'OfferAccepted' to default list of availability mappings in Dezrez JSON format
* Append floors and tenancy schedule to full description in Realla format
* Correct commercial rent frequency in Vebra format. Don't just default to pa
* Declare support for WordPress version 4.9.8

= 1.0.98 =
* Added additional warning when deleting import that has on market properties. Was causing support when people created a copy of an existing import and wondered why the old properties weren't removed from the market.
* Take into account MarketedOnInternet field in ReapIT/JET format when deciding if property should be on the market or not
* Updated Dezrez JSON format to include support for specific Branch IDs and/or Tags
* Corrected property ID field name in Realla format when comparing meta/terms and logging changes

= 1.0.97 =
* Added support for commercial tenure to BLM format
* Added new message promoting new Jupix Enquiries add on if a Jupix import is setup
* Removed line breaks from full description when importing from Jupix as they include both HTML <br>'s and line breaks which resulted in double spacing on front end.
* Tweaked Reapit / JET remove functionaliyy including new filter and improved log
* Declare support for WordPress version 4.9.7

= 1.0.96 =
* Renamed 'Jet' to 'Reapit / Jet' as they use the same API
* Remove unnecessary logs that offered no benefits. Should reduce log entries by 50%
* Do comparison of meta data and taxonomies/terms before and after importing properties, then compare and display any differences in the logs
* When logging how many photos, floorplans etc have been imported, display how many are new vs existing
* Added support for commercial properties to CSV format
* Display warning next to 'Import Frequency' setting about a high frequency and getting IP blocked

= 1.0.95 =
* Added support for Agency Pilot format
* Added new 'Email Reports' features allowing log to be emailed to specified recipient after an import completes
* Added warning and details on property record if viewing a property record that came from an automatic import

= 1.0.94 =
* Set POA correctly in JET format when applicable (i.e. when PriceQualifier field is 'PA')
* Added support to Jupix XML and Expert Agent XML formats for when the property portal add on is active to assign properties to agents and branches

= 1.0.93 =
* Added support for wp-cli. Now manually execute import by running 'wp import-properties'

= 1.0.92 =
* Cater for BST timezone when requesting properties from Vebra who seem to want dates in GMT/UTC (unsure as no mention in their docs relating to timezones)
* Declare support for WordPress version 4.9.6

= 1.0.91 =
* Don't set negotiator ID if it's been set manually or already exists
* Don't import off market or withdrawn properties in Realla format

= 1.0.90 =
* Updated JET format after restrictions added their end which caused imports to fail. The change is to not get all properties in one go now but obtain them in batches and paginate through them.

= 1.0.89 =
* Added support for Realla JSON API

= 1.0.88 =
* Add cURL fallback when obtaining property details in Dezrez XML format

= 1.0.87 =
* Write full description for commercial properties to correct field when importing them from Jupix
* Cater for translations when outputting field names in CSV mapping process
* Don't continue to import properties from ExpertAgent or CityLets when XML can't be parsed. Previously could've meant all properties were removed from market if invalid XML provided
* Add log when availability in Vebra format not mapped
* Look at SEARCHABLE_AREAS field when automatically mapping location in 10ninety
* Change re-run limit from 12 to 6 hours if nothing has happened
* Declare support for WordPress version 4.9.5

= 1.0.86 =
* Attempt to automatically assign properties to locations in Dezrez formats
* Added ability to import parking from CSV
* Corrected typo in Dezrez XML import which could cause 'Only Import Updated Properties' feature to not work

= 1.0.85 =
* Added support for Domus XML API
* If no regions mapped in Jupix format then try to automatically assign prooerties to a location by looking at the address

= 1.0.84 =
* Cater for importing new homes when using the Vebra API format
* Corrected wrong variable names being used in Jupix format which sometimes caused imports to not process
* Declare support for WordPress version 4.9.4

= 1.0.83 =
* Added support for agricultural properties to Jupix XML format. Will import them into sales with the property type set as 'Land' (if it finds a type of that name)
* Improved support for commercial availability in Jupix XML format

= 1.0.82 =
* New format, 10ninety, added to list of supported formats. This is an XML which they provide a URL to.

= 1.0.81 =
* Added support for marketing flags to CSV format
* Import available date from Thesaurus
* Corrected name of reception rooms field in JET format

= 1.0.80 =
* Added new 'chunk' advanced settings for processing records in, well, chunks. Good for reducing server load and preparation for potential process forking in future to get around timeout issues
* Added seconds to log output
* Added support for custom fields added using the Template Assistant add on in CSV format
* Don't continue processing Jupix XML if XML can't be parsed. In the past we've seen it where Jupix would be down or provide a blank XML file meaning all properties would be removed until the next import ran
* Do addslashes() when storing media URLs from ExpertAgent format. For some reason some media URLs contain backslashes which would be removed by WP.
* Added support for BLM and RTDF portals in CSV format
* Save virtual tours in CSV format. Previously you could select the field but they weren't actually saved
* Corrected use of incorrect hook name in CSV import
* Declare support for WordPress version 4.9.2

= 1.0.79 =
* Cater for EPC/HIP documents sent in columns MEDIA_DOCUMENT_{51-55}. Previous we only checked MEDIA_DOCUMENT_50
* Replace 'ftp://' protocol if entered as part of the host for formats that use FTP
* Declare support for WordPress version 4.9.1

= 1.0.78 =
* Cater for both price qualifier fields in Thesaurus format
* Declare support for WordPress version 4.9

= 1.0.77 =
* Refinement to how we determine if a property is on or off market in Dezrez JSON format
* Declare support for WordPress version 4.8.3

= 1.0.76 =
* Various updates to the REAMXML format, including adding support for virtual tours and currency
* When 'Only Import Updated Properties' is selected in BLM format, take into account that the UPDATE_DATE might be blank, in which case import the property
* Cater for invalid or empty BLMs bu ignoring them. Prevents a case where an empty BLM is sent and all properties are removed
* Declare support for WordPress version 4.8.2

= 1.0.75 =
* Added support for virtual tours to JET format
* Updated REAMXML format to cater for images sent in different ways. Some send it in <images> node, other send it in <objects> node
* Added check to automatically restart import scheduled task if for some reason it doesn't exist

= 1.0.74 =
* Added new option to BLM format to only import updated properties. Uses the UPDATE_DATE field, if provided, to determine if a property has changed and needs updating
* Declare support for WordPress version 4.8.1

= 1.0.73 =
* Added support for importing features in JET format

= 1.0.72 =
* Use Google API key when making geocoding requests for lat/lng
* Added more debugging when trying to obtain lat/lng and log errors when lat/lng can't be obtained
* In ExpertAgent format cater for when department names have been customised. Previously we would check for 'Residential Sales' for example but turns out these can be customised by the client to be just 'Sales'.

= 1.0.71 =
* Don't unlink/delete received images in BLM format until updated database in case it times out mid-import

= 1.0.70 =
* Added support for commercial properties to Jupix format

= 1.0.69 =
* Import room dimensions in Thesaurus import

= 1.0.68 =
* Prevent manual execution of import when there has been any activity in the past 5 minutes. This indicates an import might already be running and therefore could result in duplicates and other issues.

= 1.0.67 =
* Try and auto-assign property to location in Thesaurus format based on address provided
* Write log to entry when an import is executed manually, including who executed it, to assist with debugging

= 1.0.66 =
* Rooms now imported when using Thesaurus format
* Added warning when setting up Vebra import if cURL isn't enabled

= 1.0.65 =
* Automatically perform mapping where possible when creating a new import
* Redirect user to main 'Import Properties' screen when plugin is activated for the first time

= 1.0.64 =
* Added new import frequency of 'Every 15 Minutes'
* Corrections regarding dates and times output. Now save everything to the DB in GMT/UTC and then output based on timezone in 'Settngs > General'
* Added 'Exchanged' to list of default availability mappings in ExpertAgent format
* Added 'LongDesc' to list of fields to obtain when importing from JET and use this field as the description
* Added commercial support to Vebra format
* Improvements to Rentmal format including fixing availability, adding more property types to default mapping and importing available date
* Include wp-admin/includes/plugin.php in cron. This might've caused issues with import not running for some hosts

= 1.0.63 =
* Output error if local directory doesn't exist or not readable for formats trying to parse files locally
* Declare support for WordPress version 4.8

= 1.0.62 =
* Added support for Acquaint
* Added to new filters to the JET format to override the criteria used in API requests for properties
* When importing properties using the BLM format, allow for assignment to multiple locations if more than one match found
* Declare support for WordPress version 4.7.5

= 1.0.61 =
* Added new remove action to choose whether or not to delete media when properties are taken off market
* When removing properties only query properties already on market. Improves the efficiency for imports that have been running a while
* Corrected issue with imports failing to run in multisite environment
* Changed terminology of 'Running' to 'Active' on screen to avoid confusion
* Corrected a few field names and now import brochures/EPCs in JET format
* Declare support for WordPress version 4.7.4

= 1.0.60 =
* Automatically assign properties to locations imported from ExpertAgent
* Added filter to override fields requested from JET

= 1.0.59 =
* Import virtual tours from BLM files if present

= 1.0.58 =
* Fixed a couple of issues with CSV import regarding department and currency
* Added additional default mapping values to JET format

= 1.0.57 =
* Brochures now imported in Dezrez XML format

= 1.0.56 =
* Added support for the REAXML format; a common format used in the Australian real estate industry

= 1.0.55 =
* Correct usage of summary and full description in JET format

= 1.0.54 =
* Added support for Rentman XML format

= 1.0.53 =
* Add filters to dezrez feeds to allow overwriting of imported media widths

= 1.0.52 =
* Cater for when image is replaced but retains same URL in Jupix feed by checking and comparing modified date
* Updated Vebra format regarding types and qualifiers to not use rm_* fields but instead designated fields in XML
* Declare support for WordPress version 4.7.3

= 1.0.51 =
* Added support for JET software format

= 1.0.50 =
* Added ability to specify which inparticular branches properties should be imported in Dezrez format
* Output error if PHP ZipArchive class doesn't exist and trying to process ZIP files containing BLMs
* Added support to Dezrez XML format for when the property portal add on is active to assign properties to agents and branches

= 1.0.49 =
* Fixed type and price qualifier mapping for Vebra format
* Try and assign properties a location taxonomy based on address provided by Vebra
* Improvements to setting of default country across all formats

= 1.0.48 =
* Import EPC chart from Thesaurus using dynamic EPC generator and passing in EER and EIR numbers (Note: requires define('ALLOW_UNFILTERED_UPLOADS', true); in wp-config.php)
* Add link to documentation in availability mapping section to assist with this

= 1.0.47 =
* Add location mapping to Jupix format
* Try and auto-assign property to location in BLM format based on address provided

= 1.0.46 =
* Added additional logging when we receive a type that isn't mapped
* Added message when it looks like an import has fell over (i.e. not done anything in 30 minutes) suggesting the next steps to take
* Removed chance of import being ran from front end by public by using 'admin_init' action instead of 'init'

= 1.0.45 =
* Corrected CSV import failing when rows exceeded 1000 characters
* Added support for currency to CSV import, and use correct default if not provided
* Added new 'propertyhive_csv_fields' filter to allow custom fields to be added to CSV import
* Declare support for WordPress version 4.7.2

= 1.0.44 =
* Import full descriptions for commercial properties correctly

= 1.0.43 =
* Improvements to Thesaurus format after it being used in a real-world scenario
* Set the correct rental frequency in Dezrez XML format
* Add more rules to Dezrez JSON feed regarding property types to increase the chance of them being imported correctly
* Check for valid [license key](https://wp-property-hive.com/product/12-month-license-key/) before performing future updates
* Declare support for WordPress version 4.7.1

= 1.0.42 =
* Add new option to Vebra API XML format to specify whether only updated properties are imported. Good for doing a full refresh of property data
* Tweaks to 'Do Not Remove' setting when receiving a delete action from Vebra
* Make sure Vebra import uses the correct property ID when logging and deciding which properties to take off the market

= 1.0.41 =
* 'Next Due To Run At' column now a true representation of when you can expect the next import to run
* Improvements to Jupix XML format. If no lat/lng in XML, attempt to get it ourselves using the Google Geocoding API

= 1.0.40 =
* New format, Thesaurus, added to list of supported formats

= 1.0.39 =
* Added support for brochures, EPCs and virtual tours to Dezrez JSON format
* Corrections to CSV import including supporting CSVs using invalid line endings and more

= 1.0.38 =
* Tweak to Dezrez XML format to ensure property photos and floorplans updated in the future are updated correctly

= 1.0.37 =
* Cater for Sold STC status received in Dezrez XML format
* Declare support for WordPress version 4.7

= 1.0.36 =
* Add new option to Dezrez XML format to specify whether only updated properties are imported. Good for doing a full refresh of property data
* Add EPCs and virtual tours to Dezrez XML import format

= 1.0.35 =
* Check that finfo class exists before trying to validate file type.

= 1.0.34 =
* Prevent media being duplicated when ExpertAgent alternate their media URLs

= 1.0.33 =
* Add support for manual CSV upload
* Take into account 'Do Not Remove' setting when receiving a delete action from Vebra
* Added extra debugging and UTF8 encoding to BLM post inserts and updates
* Added missing save hooks and logs to end of most formats on each property iteration
* Automatically clear down old unused media when also clearing down old processed BLM files. For when we receive media for properties that we never end up importing

= 1.0.32 =
* Add support for Citylets XML format

= 1.0.31 =
* Fix issue which prevented property type from importing in Jupix feed

= 1.0.30 =
* Fix issue with importing full descriptions from BLMs

= 1.0.29 =
* Fix typo which prevented virtual tours from importing in Jupix feed

= 1.0.28 =
* Attempt to fix issue with EPC PDFs from Jupix not importing due to missing extension
* Fix typos in error messages

= 1.0.27 =
* Add support for Dezrez Rezi JSON API format
* Add ability to delete paused imports

= 1.0.26 =
* Correction to recent commercial BLM import to fix commercial properties not showing on frontend

= 1.0.25 =
* Add support for importing commercial properties from BLM format when commercial department is active

= 1.0.24 =
* Only remove properties if one or more properties we processed in DezRez and Vebra formats. Stops all properties being removed if issue with API request.
* Corrected issue with DezRez XML feed regarding it taking properties off the market that haven't been updated
* Corrected issue with DezRez XML feed regarding downloading Metropix floorplans

= 1.0.23 =
* Add a new FTP passive option to Expert Agent import options
* Consider 'propertyoftheweek' XML node in Expert Agent XML when setting featured properties
* Updated price calculation when receiving the rent frequency as PPPW in BLM files
* Attempt to solve an encoding issue when inserting the full description
* Declare support for WordPress version 4.6.1

= 1.0.22 =
* Fallback to use cURL if allow_url_open is disabled when trying to obtain URL contents
* Add warnings to setup wizard if both allow_url_open and cURL are disabled

= 1.0.21 =
* Keep BLM files for 7 days before automatically deleting them
* Add extra error logging around media uploading
* Fix a couple of errors around overwriting existing brochures and EPCs

= 1.0.20 =
* Added Vebra as a supported import format. Uses the V9 Client Feed API

= 1.0.19 =
* Fixed issue with each import not being considered independently when working out if it's ok run.
* Make checking for ExpertAgent featured property not case-sensitive to improve reliability
* ExpertAgent import to cross check both country names and country codes when trying to set the property country.
* Fixed issue with 'Last Ran Date' not showing if start and end date are the same
* Declare support for WordPress version 4.5.3

= 1.0.18 =
* Added new actions pre and post import for each format
* Corrected issue with Dezrez XML import not importing Sold STC properties
* Corrected issue with ExpertAgent XML import to cater for media URL's containing spaces

= 1.0.17 =
* Added integration support for when the property portal add on is active to assign properties to agents and branches

= 1.0.16 =
* Improve checking of annual rent frequency in ExpertAgent import

= 1.0.15 =
* Added support for Dezrez XML

= 1.0.14 =
* Fixed typo in BLM import
* Obtain lat/lng for properties sent in BLM using Google Geocoding service as we don't get that info

= 1.0.13 =
* Corrected incorrect calculation when normalising rents to monthly amounts

= 1.0.12 =
* Add Available to Let to list of Expert Agent availabilities

= 1.0.11 =
* Add Let STC to list of Expert Agent availabilities

= 1.0.10 =
* New option to disable auto-removal of properties when they're not included in imported files
* Add log entry when a property is automatically removed

= 1.0.9 =
* Added actions to execute custom code on each property imported via the various formats

= 1.0.8 =
* Added support for countries in all import formats

= 1.0.7 =
* Corrections to ensure media descriptions are corect in EA and BLM imports

= 1.0.6 =
* Fixed issue with mappings not getting set correctly
* Improvements to ExpertAgent import, including now importing price qualifier, type, POA, rent frequency, brochures, EPCs and virtual tours

= 1.0.5 =
* Huge improvements to logging
* Add fallback for when title and excerpt might go in blank due to encoding

= 1.0.4 =
* Added Jupix XML to list of supported automatic formats
* Tweaked code in various places to prevent PHP warnings

= 1.0.3 =
* Added support for one or more automatic BLM imports
* Improve cleaning up of files once they're finished with
* Small improvements to recently released ExpertAgent XML support

= 1.0.2 =
* Added support for multiple automatic feeds

= 1.0.1 =
* Large overhaul of addon to allow for automatic add ons
* Added ExpertAgent XML to list of supported automatic formats

= 1.0.0 =
* First working release of the add on