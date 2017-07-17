import { setLocale } from 'react-redux-i18n';

export default function changeLanguage(payload) {
    return function(dispatch) {
        dispatch({
            type: 'PUT_LANGUAGE_START'
        });

        dispatch(setLocale(payload.language));

        dispatch({
            type: 'PUT_LANGUAGE_COMPLETE',
            payload: {
                lang: payload.language
            }
        });

        fetch('/entry.php?action=users/userChangeLanguage&lang=' + payload.language,
            { credentials: "same-origin" }
        );
    }
};