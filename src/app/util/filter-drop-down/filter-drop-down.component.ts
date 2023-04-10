import { Component, EventEmitter, Input, Output, OnInit } from '@angular/core';

@Component({
  selector: 'app-filter-drop-down',
  templateUrl: './filter-drop-down.component.html',
  styleUrls: ['./filter-drop-down.component.css']
})
export class FilterDropDownComponent implements OnInit {

  @Input() filterVal: any;
  @Input() defaultVal: any;
  @Input() filterDisplayKey: string;
  @Input() optionTitleDisplayKey: string;
  @Input() optionTitleIsDate: boolean;
  @Input() optionValDisplayKey: string;
  @Input() optionValIsDate: boolean;
  @Input() filterName: string;
  @Input() filterOptionList: Array<string>;
  @Input() menuPlacement: string;
  @Input() showSearchBar: boolean = false;
  @Output() filterActiveCallback = new EventEmitter<boolean>();
  @Output() filterClearCallback = new EventEmitter<boolean>();

  filterOptionsVal: string = '';

  constructor() { }

  ngOnInit() {
    if (this.defaultVal) {
      this.filterVal = this.defaultVal;
    }
  }

  private activateFilter() {
    this.filterActiveCallback.emit(this.filterVal);
  }

  private clearFilter() {
    this.filterVal = undefined;
    this.filterClearCallback.emit(this.filterVal);
  }
}