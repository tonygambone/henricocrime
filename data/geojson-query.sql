-- this query returns GeoJSON-formatted features from the incident table.
-- needs to be wrapped in the outer GeoJSON structure.
select
CONCAT(
	'{"type":"Feature","geometry":{"type":"Point","coordinates":[',
	longitude,
	',',
	latitude,
	']},"properties":{',
	'"incident_id":', incident_id, ',',
	'"icr_number":"', icr_number, '"', ',',
	'"mag_district_id":', mag_district_id, ',',
	'"offense_code":"', offense_code, '"', ',',
	'"offense_text":"', offense_text, '"', ',',
	'"sequence_no":', sequence_no, ',',
	'"begin_time":"', DATE_FORMAT(begin_time, '%Y-%m-%dT%T'), '"', ',',
	'"end_time":"', DATE_FORMAT(end_time, '%Y-%m-%dT%T'), '"', ',',
	'"status_id":', status_id, ',',
	'"disposition_id":', disposition_id, ',',
	'"location_text":"', location_text, '"', ',',
	'"location_type":"', location_type, '"', ',',
	'"location_detail":"', location_detail, '"', ',',
	'"is_primary_icr":', is_primary_icr, ',',
	'"officer_name":"', officer_name, '"', ',',
	'"officer_id":', officer_id, ',',
	'"service_area":', service_area, ',',
	'"small_response_area":', small_response_area, ',',
	'"is_victim_injured":', is_victim_injured, ',',
	'"is_prop_vandalized":', is_prop_vandalized, ',',
	'"is_prop_stolen":', is_prop_stolen, ',',
	'"is_prop_found":', is_prop_found, ',',
	'"is_prop_lost":', is_prop_lost, ',',
	'"found_address":"', found_address, '"',
	'}},') as json
from incident
where latitude is not null and latitude != 0 and longitude is not null and longitude != 0;
