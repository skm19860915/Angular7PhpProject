import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-action-drop-down',
  templateUrl: './action-drop-down.component.html',
  styleUrls: ['./action-drop-down.component.css']
})
export class ActionDropDownComponent {

  @Input() actionDropDownName: string;
  @Input() selectedItemNameSin: string;
  @Input() selectedItemNamePlu: string;
  @Input() actionList: Array<string>;
  @Input() selectionCount: Number;
  @Output() actionCallback = new EventEmitter<boolean>();
  @Output() clearSelectionCallback = new EventEmitter<boolean>();

  constructor() { }

  private executeAction(action) {
    this.actionCallback.emit(action);
  }

  private clearSelection() {
    this.clearSelectionCallback.emit();
  }
}