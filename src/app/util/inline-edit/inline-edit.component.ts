import { Component, Input, ViewChild, forwardRef, ElementRef, AfterViewInit } from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';

const VALUE_ACCESSOR = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => InlineEditComponent),
  multi: true
};

@Component({
  selector: 'app-inline-edit',
  templateUrl: './inline-edit.component.html',
  providers: [VALUE_ACCESSOR],
  styleUrls: ['./inline-edit.component.css']
})
export class InlineEditComponent implements ControlValueAccessor {

  @Input() label: string = "Edit"; 
  @Input() placeholder: string = ""; 
  @Input() required: boolean = false; 
  @ViewChild('inputElement') inputElement: ElementRef;

  private _value: string = ''; 
  private preValue: string = '';
  public editing: boolean = false; 

  public onChange: any = Function.prototype;
  public onTouched: any = Function.prototype;

  get value(): any {
    return this._value;
  }

  set value(v: any) {
    if (v !== this._value) {
      this._value = v;
      this.onChange(v);
    }
  }

  writeValue(value: any) {
    this._value = value;
  }

  public registerOnChange(fn: (_: any) => {}): void {
    this.onChange = fn;
  }

  public registerOnTouched(fn: () => {}): void {
    this.onTouched = fn;
  }

  onBlur($event: Event) {
    this.editing = false;
  }

  beginEdit(value) {
    this.preValue = value;
    this.editing = true;
    setTimeout(() => this.inputElement.nativeElement.focus(), 0);
  }
}