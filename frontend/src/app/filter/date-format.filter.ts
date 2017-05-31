import * as moment from "moment";

/**
 * Форматирование даты
 */
export const dateFormatFilter = (value: string | moment.Moment | Date | number, format: string = 'D MMMM YYYY HH:mm') => {
    let m = moment(value);
    if (m.isValid()) {
        return m.format(format);
    }
    return typeof value == 'string' ? value : '';
};
