import { Pipe, PipeTransform, Injectable } from '@angular/core';

@Pipe({
  name: 'optionFilter'
})

@Injectable()
export class OptionFilterPipe implements PipeTransform {
  transform(items: any[], value: string, expression: string): any[] {
    if (!items) {
      return [];
    }
    if (!value) {
      return items;
    }
    return items.filter(function (item) {
      switch(expression) {
        case 'EQUALS':
          return item === value;
        case 'CONTAINS':
          return item.toLowerCase().includes(value.toLowerCase());
        case 'IS_NOT':
          return item !== value;
        default:
          return item === value;
      }
    })
  }
}