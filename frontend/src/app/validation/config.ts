/**
 * Конфигурация валидаторов форм
 */

export const validateConfiguration = {
    locale: 'ru',
    dictionary: {
        ru: {
            messages: {
                email: () => 'Неверный формат e-mail',
                required: () => 'Поле обязательно для заполнения'
            }
        }
    }
};
