export class TemplateTicket {
    TicketNumber: number;
    AvailableTo: string = '';
    AssociatedDeliveryType: string = 'ELECTRONIC';
    AutoApprove: boolean = false;
    AssociatedSeatType: string = '';
    Section: string = '';
    Row: string = '';
    Seat: string = '';
}