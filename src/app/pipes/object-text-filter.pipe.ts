import { Pipe, PipeTransform, Injectable } from '@angular/core';

@Pipe({
  name: 'objectTextFilter'
})

@Injectable()
export class ObjectTextFilterPipe implements PipeTransform {
  transform(items: any[], field: string, value: string): any[] {
    if (!items) {
      return [];
    }
    if (!field || !value) {
      return items;
    }
    return items.filter(function (item) {
      return item[field].toLowerCase().includes(value.toLowerCase());
    })
  }
}