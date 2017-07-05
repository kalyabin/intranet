import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import * as moment from "moment";

/**
 * Датапикер с возможностью выбора нескольких дат
 */
@Component({
    template: require('./pick-me-up.html')
})
export class PickMeUp extends Vue {
    @Prop(Array) markDays: string[];

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
            this.unmarkDay(day);
            this.$emit('unmark-day', day);
        } else {
            this.marked.push(dayIndex);
            this.$emit('mark-day', day);
        }
    }

    /**
     * Снять пометку с дня.
     * Нужен как отдельный метод для доступа из внешнего компонента
     */
    unmarkDay(day: moment.Moment): void {
        let dayIndex = day.format('YYYY-MM-DD');
        let index = this.marked.indexOf(dayIndex);
        if (index !== -1) {
            this.marked.splice(index, 1);
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
            this.currentMonth = 12;
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
