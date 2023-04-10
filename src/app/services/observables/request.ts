export class Request {
    RequestNumber: number;
    AssociatedTicketRecordNumber: number;
    AssociatedTicketRequestNumber: number;
    EventDescription: string;
    EventDateTime: Array<string>;
    statusOfTicketRequest: string;
    Seat: string;
    Section: string;
    Row: string;
    AssociatedSeatType: string;
    RequestedBy: string;
    RequestDateTime: Array<string>;
    ReasonCode: string;
    FirmAttorneyForGuest: string;
    AssociatedGuestClientNumber: number;
    GuestCompany: string;
    GuestName: string;
    GuestEmail: string;
    GuestPhoneNumber: string;
}