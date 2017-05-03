import './user-options-item.sass';

import React from 'react';

export default class UserOptionsItem extends React.Component {
    static textInput = null;

    changeHandle(event) {
        this.props.changeValue({
            [this.props.id]: this.textInput.value || ''
        });
    }

    render() {
        return (
            <div className="user-options-item row">
                <span className="user-options-item__label">{ this.props.label }</span>
                <input type="text" className='form-control'
                    id={ this.props.id }
                    value={ this.props.value }
                    onChange={ this.changeHandle.bind(this) }
                    ref={(input) => { this.textInput = input; }} />
            </div>
        );
    }
}