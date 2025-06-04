
"""
Adressbuch-Integration für das IFAK Ticketsystem
Version 2.0 - Mit Facility/Location/Department Integration
"""

import os
from datetime import datetime
from config import DATABASE
from database import query_db


def search_employees(search_term):
    """Mitarbeiter im Adressbuch suchen - mit Facility-Informationen"""
    employees = query_db("""
        SELECT DISTINCT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail,
               f.FacilityID, f.Facility, f.LocationID, f.DepartmentID,
               l.Location, d.Department
        FROM Employees e
        LEFT JOIN FacilityLinks fl ON e.EmployeeID = fl.EmployeeID
        LEFT JOIN Facilities f ON fl.FacilityID = f.FacilityID
        LEFT JOIN Locations l ON f.LocationID = l.LocationID
        LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID
        WHERE e.FirstName LIKE ? OR e.LastName LIKE ?
        ORDER BY e.LastName, e.FirstName
        LIMIT 10
    """, (f'%{search_term}%', f'%{search_term}%'), db_type='address')
    
    return [format_contact_info(emp) for emp in employees]


def get_employee_details(employee_id):
    """Details eines bestimmten Mitarbeiters holen"""
    employee = query_db("""
        SELECT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail,
               f.FacilityID, f.Facility, f.LocationID, f.DepartmentID,
               l.Location, d.Department
        FROM Employees e
        LEFT JOIN FacilityLinks fl ON e.EmployeeID = fl.EmployeeID
        LEFT JOIN Facilities f ON fl.FacilityID = f.FacilityID
        LEFT JOIN Locations l ON f.LocationID = l.LocationID
        LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID
        WHERE e.EmployeeID = ?
        LIMIT 1
    """, (employee_id,), one=True, db_type='address')
    
    return format_contact_info(employee) if employee else None


def format_contact_info(employee):
    """Mitarbeiterdaten in ein einheitliches Format für das Frontend bringen"""
    if not employee:
        return None
    
    # Telefonnummer: Mobile bevorzugt, sonst Festnetz
    phone = employee.get('Mobile') or employee.get('Phone') or ''
    
    # Name zusammenfügen
    full_name = f"{employee.get('FirstName', '')} {employee.get('LastName', '')}".strip()
    
    # Organisationsinformationen
    facility = employee.get('Facility', '')
    location = employee.get('Location', '')
    department = employee.get('Department', '')
    
    return {
        'id': employee.get('EmployeeID'),
        'name': full_name,
        'phone': phone,
        'email': employee.get('Mail', ''),
        'facility_id': employee.get('FacilityID'),
        'facility_name': facility,
        'location_id': employee.get('LocationID'),
        'location_name': location,
        'department_id': employee.get('DepartmentID'),
        'department_name': department,
        'organization_info': f"{facility} ({location})" if facility and location else facility or location
    }


def get_facility_info(facility_id):
    """Einrichtungs-Informationen abrufen"""
    return query_db("""
        SELECT f.FacilityID, f.Facility, f.LocationID, f.DepartmentID,
               l.Location, d.Department
        FROM Facilities f
        LEFT JOIN Locations l ON f.LocationID = l.LocationID
        LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID
        WHERE f.FacilityID = ?
    """, (facility_id,), one=True, db_type='address')


def get_location_info(location_id):
    """Standort-Informationen abrufen"""
    return query_db("""
        SELECT LocationID, Location, Short, Phone, Street, ZIP, Town
        FROM Locations
        WHERE LocationID = ?
    """, (location_id,), one=True, db_type='address')


def get_department_info(department_id):
    """Abteilungs-Informationen abrufen"""
    return query_db("""
        SELECT DepartmentID, Department, Short, color
        FROM Departments
        WHERE DepartmentID = ?
    """, (department_id,), one=True, db_type='address')


def get_addressbook_date():
    """Letztes Aktualisierungsdatum des Adressbuchs basierend auf Datei-Zeitstempel"""
    try:
        db_path = DATABASE['address_db']
        if os.path.exists(db_path):
            mod_time = os.path.getmtime(db_path)
            return datetime.fromtimestamp(mod_time).strftime('%d.%m.%Y %H:%M')
        return "Datei nicht gefunden"
    except Exception as e:
        print(f"Fehler beim Abrufen des Adressbuch-Datums: {e}")
        return "Nicht verfügbar"
