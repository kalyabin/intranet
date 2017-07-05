import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import * as moment from "moment";

/**
 * Датапикер с возможностью выбора нескольких дат
 */
@Component({
    template: require('./calendar-chooser.html')
})
export class CalendarChooser extends Vue {
    @Prop(Array) markDays: string[];

    /**
     * Формат для вывода даты
     */
    @Prop(String) dateFormat: string;

    @Model() markedFormat: string = this.dateFormat ? this.dateFormat : 'DD.MM.YYYY';

    /**
     * Помеченные дни
     */
    @Model() marked: string[] = this.markDays ? this.markDays : [];

    /**
     * Сегодняшний день
     */
    @Model() today: moment.Moment = moment();

    /**
     * Текущий месяц
     */
    @Model() currentMonth: number = moment().month();

    /**
     * Текущий год
     */
    @Model() currentYear: number = moment().year();

    /**
     * Недели текущего месяца
     */
    @Model() monthDays: Array<moment.Moment[]> = [];

    /**
     * Подпись текущего месяца
     */
    get currentMonthLabel(): string {
        const monthLabels = [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ];
        return monthLabels[this.currentMonth] || '';
    }

    /**
     * Названия дней неделей
     */
    get dayOfWeeks(): string[] {
        return [
            'Пн',
            'Вт',
            'Ср',
            'Чт',
            'Пт',
            'Сб',
            'Вс'
        ];
    }

    @Watch('markDays')
    onChangeMarkDays(days: string[]): void {
        this.marked = days;
    }

    /**
     * Возвращает true, если день был помечен
     */
    dayIsMarked(day: moment.Moment): boolean {
        return this.marked.indexOf(day.format('YYYY-MM-DD')) !== -1
    }

    /**
     * Клик по дню
     */
    clickDay(day: moment.Moment): void {
        let dayIndex: string = day.format('YYYY-MM-DD');
        if (this.marked.indexOf(dayIndex) != -1) {
            this.unmarkDay(dayIndex);

        } else {
            this.markDay(dayIndex);
        }
    }

    /**
     * Получить отформатированную дату
     */
    getDateFormatted(date: string | moment.Moment): string {
        return moment(date).format(this.markedFormat);
    }

    /**
     * Снять пометку с дня
     */
    unmarkDay(dayIndex: string): void {
        let index = this.marked.indexOf(dayIndex);
        if (index !== -1) {
            this.marked.splice(index, 1);
            this.$emit('unmark-day', dayIndex);
        }
    }

    /**
     * Пометить день
     */
    markDay(dayIndex: string): void {
        if (this.marked.indexOf(dayIndex) === -1) {
            this.marked.push(dayIndex);
            this.$emit('mark-day', dayIndex);
        }
    }

    /**
     * Переключение месяцев по стрелкам
     */
    toggleMonth(direction: number): void {
        let month = this.currentMonth;
        month += (direction > 0) ? 1 : -1;
        if (month > 11) {
            this.currentMonth = 0;
            this.currentYear = this.currentYear + 1;
        } else if (month < 0) {
            this.currentMonth = 11;
            this.currentYear = this.currentYear - 1;
        } else {
            this.currentMonth = month;
        }
        this.renderMonthDays();
    }

    /**
     * Формирование неделей для текущего месяца
     */
    renderMonthDays(): void {
        this.monthDays = [];

        // границы месяца
        let monthFirstDay = moment().date(1).month(this.currentMonth).year(this.currentYear);
        let monthLastDay = moment(monthFirstDay).add({
            month: 1
        }).subtract({
            day: 1
        });

        // начинаем всегда с понедельника
        let firstDay = moment(monthFirstDay).day(1);
        // заканчиваем воскресением
        let lastDay = moment(monthLastDay).day(7);

        // количество дней для рендера
        let days = lastDay.diff(firstDay, 'days');
        let curDay = moment(firstDay);
        this.monthDays.push([]);
        for (let x = 0; x <= days; x++) {
            let curWeek = this.monthDays[this.monthDays.length - 1];
            curWeek.push(moment(curDay));
            if (curDay.day() == 0 || curDay.day() == 7) {
                // воскресение, начинаем новую неделю
                this.monthDays.push([]);
            }
            curDay.add(1, 'day');
        }
    }

    mounted(): void {
        this.renderMonthDays();
    }
}
