# APIEN: A Lightweight REST API for Statistical Datasets

## DESCRIPTION
 This REST API was originally built in order to allow users to query the UNDP Human Development Report Office (HDRO) database  and return structured results in json format by ISO3 Country Code, Indicator ID and Year. In addition to these three resource selections, users have three options to select from, the language (EN, FR, SP), the json structure (c=Country, y=Year, i=Indicator) and the compression (gzipped by default).

## CONTEXT
 HDRO produces a small dataset each year which consists of approximately 60 indicators by country and year. The data is entered into a SQL Server database by the statistics unit and stored and archived there. For the API, the data is manually extracted from SQL Server and copied into a separate MySQL database. This happens at least once a year upon the publication of the global Human Development Report, which coincides with the release of the data. When this happens, the data present in the API (MySQL) database is wiped and replaced with the new data. This is due to the fact that the data is recalculated retroactively each year, in light of new methodologies and updated data. Throughout the year, when updates are made to the database, the API data may also be updated manually by the database/website manager.

## API USE
 Users should query the API using the GET method. The resource selection can be done using single or multiple strings, as follows:
 country_code = 'AFG'
 indicator_id = '72206'
 year = '1980, 1990, 2013'

 The options selection is by default set to:
 Pretty Print: pretty = true [false]
 Compression: gzip = false [true]
 Language: language = en [fr, sp]
 Data Structure: structure = ciy [ciy, yic, yci, iyc, icy]

 The pretty print option facilitates in-browser exploration of the data, however it is not necessary when downloading the data or connecting an application directly to the API.

 The GZIP format speeds up the query and is particularly useful for large queries (i.e. all indicators for all countries). It is set as a default and can be unset by using the following option selection at the end of the query: “gzip=false”.

 Gzipped files can be decompressed using any extraction software on Windows (i.e. 7Zip or WinZip) and can be extracted automatically upon download on a Mac or Linux OS. When decompressing online, refer to the language’s decompression functions (i.e. PHP: http://php.net/manual/en/function.gzdecode.php). 
 
 The resources and options above can be selected using the following query format:
 * http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/country_code/AFG,JAM/indicator_id/72206,103606/year/1980,1990,2013?structure=icy&pretty=true

 Each query will yield three json arrays: "indicator_value", which consists of the country code, indicator code and value; "country_name", which consists of the country code and the associated country name, and "indicator_name", which consists of the indicator code and the indicator name.

 An empty query (http://ec2-52-1-168-42.compute-1.amazonaws.com) will return all of the data in the HDRO database.
 
## SAMPLE QUERIES (The current year of data is for 2013)
 * Human Development Index for current year and all countries:http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/137506/year/2013
 * Human Development Index for all years and all countries:http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/137506
 * Inequality-adjusted Human Development Index for current year and all countries:http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/138806/year/2013
 * Gender Inequality Index for current year and all countries: http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/68606/year/2013
 * Gender Development Index for current year and all countries: http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/137906/year/2013
 * Multidimensional Povery Index for current year and all countries: http://ec2-52-1-168-42.compute-1.amazonaws.com/version/1/indicator_id/38406/year/2013

## USER SUPPORT
 Issues can be logged directly through GitHub. Anything related to the Human Development Report Office can be sent to hdro.web@undp.org.

## RESOURCE SELECTION INDEX FOR COUNTRIES, INDICATORS, YEARS (UPDATED: 20 MARCH 2015)

### COUNTRIES
* *country_code-English Name*
* AFG-Afghanistan
* ALB-Albania
* DZA-Algeria
* AND-Andorra
* AGO-Angola
* ATG-Antigua and Barbuda
* ARG-Argentina
* ARM-Armenia
* AUS-Australia
* AUT-Austria
* AZE-Azerbaijan
* BHS-Bahamas
* BHR-Bahrain
* BGD-Bangladesh
* BRB-Barbados
* BLR-Belarus
* BEL-Belgium
* BLZ-Belize
* BEN-Benin
* BTN-Bhutan
* BOL-Bolivia (Plurinational State of)
* BIH-Bosnia and Herzegovina
* BWA-Botswana
* BRA-Brazil
* BRN-Brunei Darussalam
* BGR-Bulgaria
* BFA-Burkina Faso
* BDI-Burundi
* KHM-Cambodia
* CMR-Cameroon
* CAN-Canada
* CPV-Cape Verde
* CAF-Central African Republic
* TCD-Chad
* CHL-Chile
* CHN-China
* COL-Colombia
* COM-Comoros
* COG-Congo
* COD-Congo (Democratic Republic of the)
* CRI-Costa Rica
* CIV-Côte d'Ivoire
* HRV-Croatia
* CUB-Cuba
* CYP-Cyprus
* CZE-Czech Republic
* DNK-Denmark
* DJI-Djibouti
* DMA-Dominica
* DOM-Dominican Republic
* ECU-Ecuador
* EGY-Egypt
* SLV-El Salvador
* GNQ-Equatorial Guinea
* ERI-Eritrea
* EST-Estonia
* ETH-Ethiopia
* FJI-Fiji
* FIN-Finland
* FRA-France
* GAB-Gabon
* GMB-Gambia
* GEO-Georgia
* DEU-Germany
* GHA-Ghana
* GRC-Greece
* GRD-Grenada
* GTM-Guatemala
* GIN-Guinea
* GNB-Guinea-Bissau
* GUY-Guyana
* HTI-Haiti
* HND-Honduras
* HKG-Hong Kong, China (SAR)
* HUN-Hungary
* ISL-Iceland
* IND-India
* IDN-Indonesia
* IRN-Iran (Islamic Republic of)
* IRQ-Iraq
* IRL-Ireland
* ISR-Israel
* ITA-Italy
* JAM-Jamaica
* JPN-Japan
* JOR-Jordan
* KAZ-Kazakhstan
* KEN-Kenya
* KIR-Kiribati
* PRK-Korea (Democratic People's Rep. of)
* KOR-Korea (Republic of)
* KWT-Kuwait
* KGZ-Kyrgyzstan
* LAO-Lao People's Democratic Republic
* LVA-Latvia
* LBN-Lebanon
* LSO-Lesotho
* LBR-Liberia
* LBY-Libya
* LIE-Liechtenstein
* LTU-Lithuania
* LUX-Luxembourg
* MKD-The former Yugoslav Republic of Macedonia
* MDG-Madagascar
* MWI-Malawi
* MYS-Malaysia
* MDV-Maldives
* MLI-Mali
* MLT-Malta
* MHL-Marshall Islands
* MRT-Mauritania
* MUS-Mauritius
* MEX-Mexico
* FSM-Micronesia (Federated States of)
* MDA-Moldova (Republic of)
* MCO-Monaco
* MNG-Mongolia
* MNE-Montenegro
* MAR-Morocco
* MOZ-Mozambique
* MMR-Myanmar
* NAM-Namibia
* NRU-Nauru
* NPL-Nepal
* NLD-Netherlands
* NZL-New Zealand
* NIC-Nicaragua
* NER-Niger
* NGA-Nigeria
* NOR-Norway
* PSE-Palestine, State of
* OMN-Oman
* PAK-Pakistan
* PLW-Palau
* PAN-Panama
* PNG-Papua New Guinea
* PRY-Paraguay
* PER-Peru
* PHL-Philippines
* POL-Poland
* PRT-Portugal
* QAT-Qatar
* ROU-Romania
* RUS-Russian Federation
* RWA-Rwanda
* KNA-Saint Kitts and Nevis
* LCA-Saint Lucia
* VCT-Saint Vincent and the Grenadines
* WSM-Samoa
* SMR-San Marino
* STP-Sao Tome and Principe
* SAU-Saudi Arabia
* SEN-Senegal
* SRB-Serbia
* SYC-Seychelles
* SLE-Sierra Leone
* SGP-Singapore
* SVK-Slovakia
* SVN-Slovenia
* SLB-Solomon Islands
* SOM-Somalia
* ZAF-South Africa
* ESP-Spain
* LKA-Sri Lanka
* SDN-Sudan
* SUR-Suriname
* SWZ-Swaziland
* SWE-Sweden
* CHE-Switzerland
* SYR-Syrian Arab Republic
* TJK-Tajikistan
* TZA-Tanzania (United Republic of)
* THA-Thailand
* TLS-Timor-Leste
* TGO-Togo
* TON-Tonga
* TTO-Trinidad and Tobago
* TUN-Tunisia
* TUR-Turkey
* TKM-Turkmenistan
* TUV-Tuvalu
* UGA-Uganda
* UKR-Ukraine
* ARE-United Arab Emirates
* GBR-United Kingdom
* USA-United States
* URY-Uruguay
* UZB-Uzbekistan
* VUT-Vanuatu
* VEN-Venezuela (Bolivarian Republic of)
* VNM-Viet Nam
* YEM-Yemen
* ZMB-Zambia
* ZWE-Zimbabwe
* SSD-South Sudan

### INDICATORS
* *id-english_name*
* 36806-Adolescent birth rate (women aged 15-19 years)
* 101406-Adult literacy rate, both sexes
* 27706-Carbon dioxide emissions per capita
* 98106-Change in forest area, 1990/2011
* 105906-Combined gross enrollment in education (both sexes)
* 103706-Education index
* 43106-Employment to population ratio, population 25+
* 123506-Estimated GNI per capita (PPP), female
* 123606-Estimated GNI per capita (PPP), male
* 69706-Expected Years of Schooling (of children)
* 123306-Expected years of schooling, females
* 123406-Expected years of schooling, males
* 38006-Expenditure on education, Public (% of GDP)
* 53906-Expenditure on health, total (% of GDP)
* 136906-Female HDI
* 130006-Female to male ratio, parliamentary seats
* 20206-GDP per capita (2011 PPP $)
* 137906-Gender Development Index (Female to male ratio of HDI)
* 68606-Gender Inequality Index value
* 141706-GNI per capita in PPP terms (constant 2011 international $)
* 38606-Headcount, percentage of population in multidimensional poverty, (revised)
* 72206-Health index
* 137506-Human development index (HDI) value
* 103606-Income index
* 135106-Income quintile ratio
* 71406-Inequality-adjusted education index
* 138806-Inequality-adjusted HDI value
* 71606-Inequality-adjusted income index
* 71506-Inequality-adjusted life expectancy index
* 38506-Intensity of deprivation
* 48906-Labour force participation rate, female-male ratio
* 69206-Life expectancy at birth
* 120606-Life expectancy at birth, female
* 121106-Life expectancy at birth, male
* 71206-Loss due to inequality in education
* 71306-Loss due to inequality in income
* 71106-Loss due to inequality in life expectancy
* 137006-Male HDI
* 89006-Maternal mortality ratio
* 24106-Mean years of schooling (females aged 25 years and above)
* 24206-Mean years of schooling (males aged 25 years and above)
* 103006-Mean years of schooling (of adults)
* 38406-Multidimensional poverty index value
* 142506-Near poor headcount
* 122006-Old-age dependency ratio, ages 65 and older
* 73506-Overall percentage loss
* 135206-Palma ratio (Highest 10% over lowest 40%)
* 101006-Population in severe poverty (headcount)
* 38906-Population living below $1.25 PPP per day
* 44706-Population living on degraded land
* 24806-Population with at least secondary education, female/male ratio
* 68006-Population, female (thousands)
* 68106-Population, male (thousands)
* 306-Population, total both sexes (thousands)
* 45106-Population, urban (%)
* 46106-Primary school dropout rates
* 45806-Primary school teachers trained to teach
* 57506-Under-five mortality rate
* 121206-Young age dependency ratio, 0-14)

### YEARS
* 1980
* 1985
* 1990
* 1995
* 2000
* 2005
* 2010
* 2011
* 2012
* 2013
