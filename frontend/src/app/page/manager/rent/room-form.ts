import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {RoomInterface} from "../../../service/model/room.interface";
import {roomManagerService} from "../../../service/room-manager.service";
import {RoomResponseInterface} from "../../../service/response/room-response.interface";
import {notificationStore} from "../../../store/notification.store";

/**
 * Форма создания или редактирования помещения
 */
@Component({
    template: require('./room-form.html'),
})
export class ManagerRoomForm extends Vue {
    /**
     * Ожидание субмита формы
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Ошибка для вывода
     */
    @Model() errorMessage: string = '';

    /**
     * Входящая комната для редактирования
     */
    @Prop(Object) inputRoom: RoomInterface;

    /**
     * Модель для заполнения
     */
    @Model() room: RoomInterface = null;

    /**
     * Субмит формы
     */
    submit(): void {
        if (this.awaitOfSubmit) {
            return;
        }

        this.errorMessage = '';

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            if (!this.inputRoom) {
                roomManagerService.create(this.room).then((response: RoomResponseInterface) => {
                    if (response.success) {
                        this.$emit('created', response.room);
                        notificationStore.dispatch('flash', {
                            text: 'Помещение успешно создано',
                            type: 'success'
                        });
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                });
            } else {
                roomManagerService.update(this.inputRoom.id, this.room).then((response: RoomResponseInterface) => {
                    if (response.success) {
                        this.$emit('updated', response.room);
                        notificationStore.dispatch('flash', {
                            text: 'Помещение успешно отредактировано',
                            type: 'success'
                        });
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                });
            }
        }, () => {});
    }

    /**
     * Добавить праздник
     */
    addHoliday(date: string): void {
        if (this.room.holidays.indexOf(date) === -1) {
            this.room.holidays.push(date);
        }
    }

    /**
     * Удалить праздник
     */
    removeHoliday(date: string): void {
        let index = this.room.holidays.indexOf(date);
        if (index != -1) {
            this.room.holidays.splice(index, 1);
        }
    }

    /**
     * Добавить перенесённый выходной
     */
    addWorkWeekend(date: string): void {
        if (this.room.workWeekends.indexOf(date) === -1) {
            this.room.workWeekends.push(date);
        }
    }

    /**
     * Удалить перенесённый выходной
     */
    removeWorkWeekend(date: string): void {
        let index = this.room.workWeekends.indexOf(date);
        if (index != -1) {
            this.room.workWeekends.splice(index, 1);
        }
    }

    @Watch('inputRoom', {
        immediate: true
    })
    onChangeInputRoom(): void {
        this.room = this.inputRoom ? this.inputRoom : {
            type: 'meeting',
            title: '',
            description: '',
            address: '',
            hourlyCost: null,
            schedule: [],
            scheduleBreak: [],
            holidays: [],
            workWeekends: [],
            requestPause: null
        };
        if (this.room.schedule.length < 7) {
            while (this.room.schedule.length < 7) {
                this.room.schedule.push([
                    {
                        avail: true,
                        from: '00:00',
                        to: '24:00'
                    }
                ]);
            }
        }
        if (this.room.scheduleBreak.length < 1) {
            this.room.scheduleBreak = [
                {
                    avail: false,
                    from: '13:00',
                    to: '14:00'
                }
            ];
        }
    }

    /**
     * Получить название дня недели по индексу
     */
    getWeekDay(index: number): string {
        let weekdays = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
            'Суббота',
            'Воскресение',
        ];
        return weekdays[index] || '';
    }

    get hasCommonError(): boolean {
        let errors = this.$validator.errorBag;
        return errors.has('type') || errors.has('title') || errors.has('description') || errors.has('address') || errors.has('hourlyCost') || errors.has('requestPause');
    }

    get hasScheduleError(): boolean {
        let errors = this.$validator.errorBag;
        if (errors.has('schedule') || errors.has('scheduleBreak')) {
            return true;
        }
        for (let i = 0; i < 7; i++) {
            if (this.hasScheduleItemError(i)) {
                return true;
            }
        }
        return errors.has('scheduleBreakFrom') || errors.has('scheduleBreakTo');
    }

    hasScheduleItemError(index: number): boolean {
        let errors = this.$validator.errorBag;
        return errors.has('scheduleAvail' + index) || errors.has('scheduleFrom' + index) || errors.has('scheduleTo' + index);
    }

    /**
     * Отмена редактирования
     */
    cancel(): void {
        this.$emit('cancel');
    }
}
