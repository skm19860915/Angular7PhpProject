import { Pipe, PipeTransform, Injectable } from '@angular/core';

@Pipe({
  name: 'objectFilter'
})

@Injectable()
export class ObjectFilterPipe implements PipeTransform {
  transform(items: any[], field: string, value: string, expression: string): any[] {
    if (!items) {
      return [];
    }
    if (!field || !value) {
      return items;
    }
    return items.filter(function (item) {
      switch(expression) {
        case 'EQUALS':
          return item[field] === value;
        case 'CONTAINS':
          return item[field].toLowerCase().includes(value.toLowerCase());
        case 'IS_NOT':
          return item[field] !== value;
        default:
          return item[field] === value;
      }
    })
  }
}