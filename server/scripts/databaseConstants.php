<?php

// SQL Server: Connection Setup Information
// define("DB_SERVERNAME", "184.168.194.77");
// define("DB_USERNAME", "wheventdb");
// define("DB_PASSWORD", "@Evh5150");

// Database Name
//define("DB_NAME", "dbTicketing");

include_once('databaseConnection.php');

// Database Table List Names
define("DB_TABLELIST_FOOD_ORDERS", "listFoodOrderCategories");
define("DB_TABLELIST_ASSIGNMENT_STATUS", "listTicketAssignmentStatus");
define("DB_TABLELIST_TICKET_CATEGORIES", "listTicketCategories");
define("DB_TABLELIST_TICKET_REQUEST_STATUS", "listTicketRequestStatus");
define("DB_TABLELIST_TICKET_SEAT_TYPES", "listTicketSeatTypes");
define("DB_TABLELIST_TICKET_STATUS", "listTicketStatus");
define("DB_TABLELIST_TICKET_USAGE_REASONS", "listTicketUsageReasons");

// Database Table Names
define("DB_TABLE_SPECIAL_TICKET_REQUESTS", "tblSpecialTicketRequests");
define("DB_TABLE_TICKET_REQUESTS", "tblTicketRequests");
define("DB_TABLE_TICKET_ASSIGNMENTS", "tblTicketAssignments");
define("DB_TABLE_TICKET_MASTER", "tblTicketsMaster");
define("DB_TABLE_TICKET_DETAIL", "tblTicketsDetail");
define("DB_TABLE_TICKET_FOOD_ORDER_MASTER", "tblFoodOrderMaster");
define("DB_TABLE_TICKET_FOOD_ORDER_DETAIL", "tblFoodOrderDetail");
define("DB_TABLE_TICKET_FOOD_ORDERING_ITEMS", "tblFoodOrderingItems");
define("DB_TABLE_EVENTS", "tblEvents");
define("DB_TABLE_EVENT_VENUES", "tblEventVenues");
define("DB_TABLE_SYSTEM_USERS", "tblSystemUsers");
define("DB_TABLE_LOGGING","tblLogging");
define("DB_TICKET_REQUEST_NOTES","tblTicketRequestNotes");
define("DB_TABLE_TICKET_TEMPLATES", "tblTicketTemplates");
define("DB_TABLE_TICKET_TEMPLATE_TICKETS", "tblTicketTemplatesTickets");
define("DB_TABLE_EVENT_TEMPLATES", "tblEventTemplates");
define("DB_TABLE_EVENT_TEMPLATE_EVENTS", "tblEventTemplatesEvents");

// Database View Names
define("DB_VIEW_ALL_TICKETS", "viewAllTickets");
define("DB_VIEW_ALL_REQUESTS_WITH_TICKET_DETAILS", "viewAllTicketRequests");
define("DB_VIEW_TICKET_REQUEST_GUESTS", "viewTicketRequestGuests");
define("DB_VIEW_TICKET_ASSIGNMENT_GUESTS", "viewTicketAssignmentGuests");

define("DB_TABLE_TEMPLATES", "dbo.Templates");
define("DB_TABLE_TICKETS_IN_TEMPLATES", "dbo.TicketsInTemplates");
define("DB_TABLE_DELIVERY_TYPES", "dbo.DeliveryTypes");

// Misc. Database 
define("DB_SINGLE_QUOTE", "'");

?>