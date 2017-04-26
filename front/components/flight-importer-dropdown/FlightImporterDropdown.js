import './flight-importer-dropdown.sass';

import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import onClickOutside from 'react-onclickoutside';
import FileInput from 'react-file-input';

class FlightImporterDropdown extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isShown: false
        };
    }

    handleClickOutside(event) {
        if ((event.target.className.includes('flight-importer-dropdown-toggle'))
            && !this.state.isShown
        ) {
            this.setState({ isShown: true });
            return;
        }

        this.setState({ isShown: false });
    }

    handleChange() {
        this.setState({ isShown: false });
        let form = new FormData(this.importFlightForm);
        this.props.topMenuService.importItem(form);
    }

    render() {
        return (
            <ul className={ "flight-importer-dropdown dropdown-menu " + ( this.state.isShown ? 'is-shown' : '' ) }>
              <li><a href="#"><b>{ this.props.i18n.fileImport }</b></a></li>
              <li><a href="#">
                  <form action="" ref={ (form) => { this.importFlightForm = form; }}>
                      <FileInput
                         className="btn btn-default"
                         name="flightFileArchive"
                         placeholder={ this.props.i18n.chooseFile }
                         value={ this.state.file }
                         onChange={ this.handleChange.bind(this) }
                       />
                  </form>
             </a></li>
            </ul>
        );
    }
}

export default onClickOutside(FlightImporterDropdown);
