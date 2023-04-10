export class Ticket {
    AvailableTo: string;
    AutoApprove: string;
    AssociatedEventNumber: number;
    AssociatedVenueNumber: number;
    AssociatedTicketCategory: string;
    AssociatedSeatType: string;
    CreatedBy: string;
    TicketRecordNumber: number;
    Status: string;
    Section: string;
    Row: string;
    Seat: string;
    FoodOrdering: string;
    TicketStatus: string;
    TicketBatchStatus: string;
    TicketDeliveryType: string = 'ELECTRONIC';
    //DeliveryType: string;
    Name: string;
    // For new ticket creation
    newTicketIndex: number;
}