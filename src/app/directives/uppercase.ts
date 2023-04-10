import { Directive, Input, Output, EventEmitter, OnInit } from '@angular/core';

@Directive({
    selector: '[uppercase]',
    host: {
        '[value]': 'uppercase',
        '(input)': 'format($event.target.value)'
    }
})
export class Uppercase implements OnInit {

    @Input() uppercase: string;
    @Output() uppercaseChange: EventEmitter<string> = new EventEmitter<string>();

    constructor() {
    }

    ngOnInit() {
        this.uppercase = this.uppercase || '';
        this.format(this.uppercase);
    }

    format(value) {
        if(value) {
            value = value.toUpperCase();
            this.uppercaseChange.next(value);
        }
    }
}