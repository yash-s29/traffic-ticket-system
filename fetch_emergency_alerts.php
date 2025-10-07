<?php
session_start();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');
$alerts = [];

$query = "
    SELECT 
        t.ticket_id,
        t.ticket_date,
        t.issue_date,
        t.status,
        t.emergency_level,
        t.fine_amount,
        t.remarks,
        v.vehicle_number,
        v.vehicle_type,
        d.driver_name,
        d.driver_contact,
        vio.violation_name,
        vio.description AS violation_description
    FROM Tickets t
    JOIN Vehicles v ON t.vehicle_id = v.vehicle_id
    JOIN Drivers d ON t.driver_id = d.driver_id
    JOIN Violations vio ON t.violation_id = vio.violation_id
    WHERE t.emergency_level IN ('High','Critical')
    ORDER BY FIELD(t.emergency_level,'Critical','High'), t.issue_date DESC
    LIMIT 20
";

$result = $conn->query($query);

if($result){
    while($row = $result->fetch_assoc()){
        $alerts[] = [
            "ticket_id" => (int)$row['ticket_id'],
            "ticket_date" => $row['ticket_date'] ?? '',
            "issue_date" => $row['issue_date'] ?? '',
            "status" => $row['status'] ?? '',
            "emergency_level" => $row['emergency_level'] ?? 'Normal',
            "fine_amount" => (float)($row['fine_amount'] ?? 0),
            "remarks" => $row['remarks'] ?? '',
            "vehicle_number" => $row['vehicle_number'] ?? '',
            "vehicle_type" => $row['vehicle_type'] ?? '',
            "driver_name" => $row['driver_name'] ?? '',
            "driver_contact" => $row['driver_contact'] ?? '',
            "violation_name" => $row['violation_name'] ?? '',
            "violation_description" => $row['violation_description'] ?? ''
        ];
    }
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch alerts"]);
    exit;
}
echo json_encode($alerts, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
